<?php

declare(strict_types=1);

namespace JuegosTenerife\Services\Import;

use JuegosTenerife\Core\Database;
use JuegosTenerife\Services\Maps\GoogleMapsCoordinateExtractor;
use JuegosTenerife\Services\Support\Slugger;
use OpenSpout\Reader\XLSX\Reader;
use PDO;
use RuntimeException;

final class EntityImportService
{
    private ExcelPreviewService $excel;
    private GoogleMapsCoordinateExtractor $maps;

    public function __construct()
    {
        $this->excel = new ExcelPreviewService();
        $this->maps = new GoogleMapsCoordinateExtractor();
    }

    /**
     * @return array<string, int>
     */
    public function import(string $filePath, string $originalFilename, ?int $userId): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException('No se encontró el archivo pendiente de importar.');
        }

        $pdo = Database::connection();
        $reader = new Reader();
        $reader->open($filePath);

        $summary = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        $pdo->beginTransaction();

        try {
            $importId = $this->createImport($pdo, $originalFilename, $filePath, $userId);

            foreach ($reader->getSheetIterator() as $sheet) {
                $headers = [];

                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $values = $this->excel->normalizeRow($row->toArray());

                    if ($this->excel->isEmptyRow($values)) {
                        continue;
                    }

                    if ($headers === []) {
                        $headers = $values;
                        continue;
                    }

                    $summary['total']++;
                    $rowMap = $this->excel->mapRow($headers, $values);
                    $warnings = $this->excel->validateRow($rowMap, $rowIndex);

                    try {
                        $result = $this->importRow($pdo, $rowMap);
                        $summary[$result]++;
                        $this->recordImportRow($pdo, $importId, $rowIndex, $result, $rowMap, $warnings, [], $result === 'skipped' ? null : $this->findEntityId($pdo, (string) $rowMap['Nombre Entidad']));
                    } catch (RuntimeException $exception) {
                        $summary['errors']++;
                        $this->recordImportRow($pdo, $importId, $rowIndex, 'error', $rowMap, $warnings, [$exception->getMessage()], null);
                    }
                }

                break;
            }

            $this->completeImport($pdo, $importId, $summary);
            $pdo->commit();
        } catch (\Throwable $throwable) {
            $pdo->rollBack();
            throw new RuntimeException('No se pudo completar la importación: ' . $throwable->getMessage(), 0, $throwable);
        } finally {
            $reader->close();
        }

        return $summary;
    }

    /**
     * @param array<string, string> $row
     */
    private function importRow(PDO $pdo, array $row): string
    {
        $name = trim($row['Nombre Entidad'] ?? '');
        if ($name === '') {
            return 'skipped';
        }

        $entityTypeId = $this->upsertSimpleLookup($pdo, 'entity_types', (string) ($row['Tipo Entidad'] ?? 'Sin tipo'));
        $municipalityId = $this->upsertMunicipality($pdo, (string) ($row['Municipio'] ?? ''));
        $slug = $this->uniqueSlugForEntity($pdo, $name, (string) ($row['Municipio'] ?? ''));
        $existingId = $this->findEntityIdBySlug($pdo, $slug);

        $statement = $pdo->prepare(
            'INSERT INTO entities (
                entity_type_id, municipality_id, name, slug, address, locality, postal_code, website_url,
                history, corporate_principles, sports_values, total_teams, teams_by_gender, teams_by_age,
                total_practitioners, female_practitioners, male_practitioners, training_practices, training_days,
                training_hours, has_board, board_members, board_male, board_female, holds_annual_assemblies,
                has_members, total_members, male_members, female_members, equality_protocol_status,
                violence_protocol_status, lopivi_status, joined_educar_entrenando, supports_educational_needs,
                supports_disability, source_reference
            ) VALUES (
                :entity_type_id, :municipality_id, :name, :slug, :address, :locality, :postal_code, :website_url,
                :history, :corporate_principles, :sports_values, :total_teams, :teams_by_gender, :teams_by_age,
                :total_practitioners, :female_practitioners, :male_practitioners, :training_practices, :training_days,
                :training_hours, :has_board, :board_members, :board_male, :board_female, :holds_annual_assemblies,
                :has_members, :total_members, :male_members, :female_members, :equality_protocol_status,
                :violence_protocol_status, :lopivi_status, :joined_educar_entrenando, :supports_educational_needs,
                :supports_disability, :source_reference
            )
            ON DUPLICATE KEY UPDATE
                entity_type_id = VALUES(entity_type_id),
                municipality_id = VALUES(municipality_id),
                name = VALUES(name),
                address = VALUES(address),
                locality = VALUES(locality),
                postal_code = VALUES(postal_code),
                website_url = VALUES(website_url),
                history = VALUES(history),
                corporate_principles = VALUES(corporate_principles),
                sports_values = VALUES(sports_values),
                total_teams = VALUES(total_teams),
                teams_by_gender = VALUES(teams_by_gender),
                teams_by_age = VALUES(teams_by_age),
                total_practitioners = VALUES(total_practitioners),
                female_practitioners = VALUES(female_practitioners),
                male_practitioners = VALUES(male_practitioners),
                training_practices = VALUES(training_practices),
                training_days = VALUES(training_days),
                training_hours = VALUES(training_hours),
                has_board = VALUES(has_board),
                board_members = VALUES(board_members),
                board_male = VALUES(board_male),
                board_female = VALUES(board_female),
                holds_annual_assemblies = VALUES(holds_annual_assemblies),
                has_members = VALUES(has_members),
                total_members = VALUES(total_members),
                male_members = VALUES(male_members),
                female_members = VALUES(female_members),
                equality_protocol_status = VALUES(equality_protocol_status),
                violence_protocol_status = VALUES(violence_protocol_status),
                lopivi_status = VALUES(lopivi_status),
                joined_educar_entrenando = VALUES(joined_educar_entrenando),
                supports_educational_needs = VALUES(supports_educational_needs),
                supports_disability = VALUES(supports_disability),
                source_reference = VALUES(source_reference)'
        );

        $statement->execute([
            'entity_type_id' => $entityTypeId,
            'municipality_id' => $municipalityId,
            'name' => $name,
            'slug' => $slug,
            'address' => $this->nullable($row['Domicilio'] ?? ''),
            'locality' => $this->nullable($row['Localidad'] ?? ''),
            'postal_code' => $this->nullable($row['Código Postal'] ?? ''),
            'website_url' => $this->nullable($row['Web'] ?? ''),
            'history' => $this->nullable($row['Breve Historia'] ?? ''),
            'corporate_principles' => $this->nullable($row['Principios Corporativos'] ?? ''),
            'sports_values' => $this->nullable($row['Valores Deportivos'] ?? ''),
            'total_teams' => $this->intOrNull($row['Equipos'] ?? ''),
            'teams_by_gender' => $this->nullable($row['Equipos por Género'] ?? ''),
            'teams_by_age' => $this->nullable($row['Equipos por Edad'] ?? ''),
            'total_practitioners' => $this->intOrNull($row['Total Deportistas/Practicantes'] ?? ''),
            'female_practitioners' => $this->intOrNull($row['Mujeres/Niñas'] ?? ''),
            'male_practitioners' => $this->intOrNull($row['Hombres/Niños'] ?? ''),
            'training_practices' => $this->nullable($row['Entrenamientos/Prácticas'] ?? ''),
            'training_days' => $this->nullable($row['Días'] ?? ''),
            'training_hours' => $this->nullable($row['Horarios'] ?? ''),
            'has_board' => $this->yesNoOrNull($row['Directiva'] ?? ''),
            'board_members' => $this->intOrNull($row['Miembros Directiva'] ?? ''),
            'board_male' => $this->intOrNull($row['Directivos/Hombres'] ?? ''),
            'board_female' => $this->intOrNull($row['Directivas/Mujeres'] ?? ''),
            'holds_annual_assemblies' => $this->yesNoOrNull($row['Asambleas Anuales'] ?? ''),
            'has_members' => $this->yesNoOrNull($row['Socios/as'] ?? ''),
            'total_members' => $this->nullable($row['Número Total Socios/as'] ?? ''),
            'male_members' => $this->nullable($row['Socios/Hombres'] ?? ''),
            'female_members' => $this->nullable($row['Socias/Mujeres'] ?? ''),
            'equality_protocol_status' => $this->protocolStatus($row['Protocolo Igualdad'] ?? ''),
            'violence_protocol_status' => $this->protocolStatus($row['Protocolo Violencia'] ?? ''),
            'lopivi_status' => $this->protocolStatus($row['LOPIVI'] ?? ''),
            'joined_educar_entrenando' => $this->yesNoOrNull($row['Educar Entrenando'] ?? ''),
            'supports_educational_needs' => $this->yesNoOrNull($row['Necesidades Educativas'] ?? ''),
            'supports_disability' => $this->yesNoOrNull($row['Discapacidad'] ?? ''),
            'source_reference' => 'excel',
        ]);

        $entityId = $this->findEntityIdBySlug($pdo, $slug);
        if ($entityId === null) {
            throw new RuntimeException('No se pudo recuperar la entidad importada.');
        }

        $this->replaceModalities($pdo, $entityId, $row);
        $this->replaceContacts($pdo, $entityId, $row);
        $this->replaceSocialLinks($pdo, $entityId, $row);
        $this->replaceFacilities($pdo, $entityId, $row);
        $this->replaceAgeRanges($pdo, $entityId, $row);

        return $existingId === null ? 'created' : 'updated';
    }

    private function createImport(PDO $pdo, string $originalFilename, string $storedPath, ?int $userId): int
    {
        $statement = $pdo->prepare(
            'INSERT INTO imports (user_id, original_filename, stored_path, status)
             VALUES (:user_id, :original_filename, :stored_path, :status)'
        );
        $statement->execute([
            'user_id' => $userId,
            'original_filename' => $originalFilename,
            'stored_path' => $storedPath,
            'status' => 'processing',
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * @param array<string, int> $summary
     */
    private function completeImport(PDO $pdo, int $importId, array $summary): void
    {
        $status = $summary['errors'] > 0 ? 'completed_with_errors' : 'completed';
        $statement = $pdo->prepare(
            'UPDATE imports
             SET status = :status, total_rows = :total_rows, created_rows = :created_rows,
                 updated_rows = :updated_rows, skipped_rows = :skipped_rows, error_rows = :error_rows,
                 summary = :summary, completed_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute([
            'status' => $status,
            'total_rows' => $summary['total'],
            'created_rows' => $summary['created'],
            'updated_rows' => $summary['updated'],
            'skipped_rows' => $summary['skipped'],
            'error_rows' => $summary['errors'],
            'summary' => json_encode($summary, JSON_UNESCAPED_UNICODE),
            'id' => $importId,
        ]);
    }

    /**
     * @param array<string, string> $rawData
     * @param array<int, string> $warnings
     * @param array<int, string> $errors
     */
    private function recordImportRow(PDO $pdo, int $importId, int $rowNumber, string $status, array $rawData, array $warnings, array $errors, ?int $entityId): void
    {
        $statement = $pdo->prepare(
            'INSERT INTO import_rows (import_id, source_row_number, status, entity_id, raw_data, warnings, errors)
             VALUES (:import_id, :source_row_number, :status, :entity_id, :raw_data, :warnings, :errors)'
        );
        $statement->execute([
            'import_id' => $importId,
            'source_row_number' => $rowNumber,
            'status' => $status === 'created' || $status === 'updated' || $status === 'skipped' ? $status : 'error',
            'entity_id' => $entityId,
            'raw_data' => json_encode($rawData, JSON_UNESCAPED_UNICODE),
            'warnings' => json_encode($warnings, JSON_UNESCAPED_UNICODE),
            'errors' => json_encode($errors, JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function upsertSimpleLookup(PDO $pdo, string $table, string $name): int
    {
        $name = trim($name) !== '' ? trim($name) : 'Sin definir';
        $slug = Slugger::slug($name);
        $statement = $pdo->prepare("INSERT INTO {$table} (name, slug) VALUES (:name, :slug) ON DUPLICATE KEY UPDATE name = VALUES(name)");
        $statement->execute(['name' => $name, 'slug' => $slug]);
        $select = $pdo->prepare("SELECT id FROM {$table} WHERE slug = :slug LIMIT 1");
        $select->execute(['slug' => $slug]);

        return (int) $select->fetchColumn();
    }

    private function upsertMunicipality(PDO $pdo, string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $slug = Slugger::slug($name);
        $isTenerife = $name !== 'Agüimes';
        $statement = $pdo->prepare(
            'INSERT INTO municipalities (name, slug, island, is_tenerife, is_filterable, sort_order)
             VALUES (:name, :slug, :island, :is_tenerife, :is_filterable, :sort_order)
             ON DUPLICATE KEY UPDATE name = VALUES(name)'
        );
        $statement->execute([
            'name' => $name,
            'slug' => $slug,
            'island' => $isTenerife ? 'Tenerife' : 'Gran Canaria',
            'is_tenerife' => $isTenerife ? 1 : 0,
            'is_filterable' => 0,
            'sort_order' => $isTenerife ? 100 : 900,
        ]);
        $select = $pdo->prepare('SELECT id FROM municipalities WHERE slug = :slug LIMIT 1');
        $select->execute(['slug' => $slug]);

        return (int) $select->fetchColumn();
    }

    private function uniqueSlugForEntity(PDO $pdo, string $name, string $municipality): string
    {
        $slug = Slugger::slug($name);
        $duplicate = $pdo->prepare('SELECT COUNT(*) FROM entities WHERE slug = :slug AND name <> :name');
        $duplicate->execute(['slug' => $slug, 'name' => $name]);

        if ((int) $duplicate->fetchColumn() === 0) {
            return $slug;
        }

        return Slugger::slug($name . '-' . $municipality);
    }

    private function findEntityId(PDO $pdo, string $name): ?int
    {
        $statement = $pdo->prepare('SELECT id FROM entities WHERE name = :name ORDER BY id DESC LIMIT 1');
        $statement->execute(['name' => $name]);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    private function findEntityIdBySlug(PDO $pdo, string $slug): ?int
    {
        $statement = $pdo->prepare('SELECT id FROM entities WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    /**
     * @param array<string, string> $row
     */
    private function replaceModalities(PDO $pdo, int $entityId, array $row): void
    {
        $pdo->prepare('DELETE FROM entity_modalities WHERE entity_id = :entity_id')->execute(['entity_id' => $entityId]);

        foreach (['Modalidad1', 'Modalidad2', 'Modalidad3', 'Modalidad4'] as $index => $field) {
            $name = trim($row[$field] ?? '');
            if ($name === '') {
                continue;
            }

            $modalityId = $this->upsertSimpleLookup($pdo, 'modalities', $name);
            $statement = $pdo->prepare('INSERT IGNORE INTO entity_modalities (entity_id, modality_id, sort_order) VALUES (:entity_id, :modality_id, :sort_order)');
            $statement->execute(['entity_id' => $entityId, 'modality_id' => $modalityId, 'sort_order' => ($index + 1) * 10]);
        }
    }

    /**
     * @param array<string, string> $row
     */
    private function replaceContacts(PDO $pdo, int $entityId, array $row): void
    {
        $pdo->prepare('DELETE FROM entity_contacts WHERE entity_id = :entity_id')->execute(['entity_id' => $entityId]);
        $insert = $pdo->prepare(
            'INSERT INTO entity_contacts (entity_id, contact_type, label, person_name, role_title, phone, email, value, is_primary, sort_order)
             VALUES (:entity_id, :contact_type, :label, :person_name, :role_title, :phone, :email, :value, :is_primary, :sort_order)'
        );

        foreach ([['Teléfono1', 10], ['Teléfono2', 20]] as [$field, $sort]) {
            if (($row[$field] ?? '') !== '') {
                $insert->execute(['entity_id' => $entityId, 'contact_type' => 'phone', 'label' => $field, 'person_name' => null, 'role_title' => null, 'phone' => $row[$field], 'email' => null, 'value' => $row[$field], 'is_primary' => $sort === 10 ? 1 : 0, 'sort_order' => $sort]);
            }
        }

        foreach ([['Email1', 30], ['Email2', 40]] as [$field, $sort]) {
            if (($row[$field] ?? '') !== '') {
                $insert->execute(['entity_id' => $entityId, 'contact_type' => 'email', 'label' => $field, 'person_name' => null, 'role_title' => null, 'phone' => null, 'email' => $row[$field], 'value' => $row[$field], 'is_primary' => $sort === 30 ? 1 : 0, 'sort_order' => $sort]);
            }
        }

        if (($row['Persona Contacto'] ?? '') !== '') {
            $insert->execute([
                'entity_id' => $entityId,
                'contact_type' => 'person',
                'label' => 'Persona de contacto',
                'person_name' => $row['Persona Contacto'],
                'role_title' => $this->nullable($row['Cargo Contacto'] ?? ''),
                'phone' => $this->nullable(trim(($row['Teléfono1 #23'] ?? '') . ' ' . ($row['Teléfono2 #24'] ?? ''))),
                'email' => $this->nullable($row['Email'] ?? ''),
                'value' => $row['Persona Contacto'],
                'is_primary' => 1,
                'sort_order' => 50,
            ]);
        }
    }

    /**
     * @param array<string, string> $row
     */
    private function replaceSocialLinks(PDO $pdo, int $entityId, array $row): void
    {
        $pdo->prepare('DELETE FROM entity_social_links WHERE entity_id = :entity_id')->execute(['entity_id' => $entityId]);
        $insert = $pdo->prepare('INSERT INTO entity_social_links (entity_id, platform, label, url, sort_order) VALUES (:entity_id, :platform, :label, :url, :sort_order)');
        $fields = ['Facebook' => 'facebook', 'Instagram' => 'instagram', 'Youtube' => 'youtube', 'X' => 'x', 'TikTok' => 'tiktok'];

        $sort = 10;
        foreach ($fields as $field => $platform) {
            if (($row[$field] ?? '') === '') {
                continue;
            }

            $insert->execute(['entity_id' => $entityId, 'platform' => $platform, 'label' => $field, 'url' => $row[$field], 'sort_order' => $sort]);
            $sort += 10;
        }
    }

    /**
     * @param array<string, string> $row
     */
    private function replaceFacilities(PDO $pdo, int $entityId, array $row): void
    {
        $pdo->prepare('DELETE FROM entity_facilities WHERE entity_id = :entity_id')->execute(['entity_id' => $entityId]);

        for ($i = 1; $i <= 9; $i++) {
            $raw = trim($row['Instalaciones' . $i] ?? '');
            if ($raw === '') {
                continue;
            }

            $facility = $this->parseFacility($raw);
            $municipalityId = $this->upsertMunicipality($pdo, $facility['municipality']);
            $slug = Slugger::slug($facility['name'] . '-' . $facility['municipality']);
            $coordinates = $facility['google_maps_url'] !== null ? $this->maps->extract($facility['google_maps_url']) : null;

            $statement = $pdo->prepare(
                'INSERT INTO facilities (municipality_id, name, slug, google_maps_url, latitude, longitude, geocoding_status, notes)
                 VALUES (:municipality_id, :name, :slug, :google_maps_url, :latitude, :longitude, :geocoding_status, :notes)
                 ON DUPLICATE KEY UPDATE
                    municipality_id = VALUES(municipality_id),
                    google_maps_url = VALUES(google_maps_url),
                    latitude = VALUES(latitude),
                    longitude = VALUES(longitude),
                    geocoding_status = VALUES(geocoding_status),
                    notes = VALUES(notes)'
            );
            $statement->execute([
                'municipality_id' => $municipalityId,
                'name' => $facility['name'],
                'slug' => $slug,
                'google_maps_url' => $facility['google_maps_url'],
                'latitude' => $coordinates['lat'] ?? null,
                'longitude' => $coordinates['lng'] ?? null,
                'geocoding_status' => $coordinates === null ? 'pending' : 'resolved',
                'notes' => $raw,
            ]);

            $select = $pdo->prepare('SELECT id FROM facilities WHERE slug = :slug LIMIT 1');
            $select->execute(['slug' => $slug]);
            $facilityId = (int) $select->fetchColumn();

            $link = $pdo->prepare('INSERT IGNORE INTO entity_facilities (entity_id, facility_id, label, sort_order) VALUES (:entity_id, :facility_id, :label, :sort_order)');
            $link->execute(['entity_id' => $entityId, 'facility_id' => $facilityId, 'label' => 'Instalación ' . $i, 'sort_order' => $i * 10]);
        }
    }

    /**
     * @param array<string, string> $row
     */
    private function replaceAgeRanges(PDO $pdo, int $entityId, array $row): void
    {
        $pdo->prepare('DELETE FROM entity_age_ranges WHERE entity_id = :entity_id')->execute(['entity_id' => $entityId]);
        $ranges = [
            'age_0_5' => 'Edades: De 0 a 5 años',
            'age_6_11' => 'Edades: De 6 a 11 años',
            'age_12_17' => 'Edades: De 12 a 17 años',
            'age_18_29' => 'Edades: De 18 a 29 años',
            'age_30_45' => 'Edades: De 30 a 45 años',
            'age_46_59' => 'Edades: De 46 a 59 años',
            'age_60_plus' => 'Edades: 60 años y más',
        ];
        $insert = $pdo->prepare(
            'INSERT INTO entity_age_ranges (entity_id, age_range_key, label, practitioners_count, raw_value, sort_order)
             VALUES (:entity_id, :age_range_key, :label, :practitioners_count, :raw_value, :sort_order)'
        );
        $sort = 10;

        foreach ($ranges as $key => $label) {
            $raw = trim($row[$label] ?? '');
            if ($raw === '') {
                continue;
            }

            $insert->execute([
                'entity_id' => $entityId,
                'age_range_key' => $key,
                'label' => $label,
                'practitioners_count' => $this->intOrNull($raw),
                'raw_value' => $raw,
                'sort_order' => $sort,
            ]);
            $sort += 10;
        }
    }

    /**
     * @return array{municipality: string, name: string, google_maps_url: ?string}
     */
    private function parseFacility(string $raw): array
    {
        preg_match('~https?://\\S+~', $raw, $urlMatch);
        $url = $urlMatch[0] ?? null;
        $text = trim($url !== null ? str_replace($url, '', $raw) : $raw);
        $parts = array_map('trim', explode(':', $text, 2));
        $municipality = $parts[0] ?? '';
        $name = $parts[1] ?? $text;

        return [
            'municipality' => $municipality !== '' ? $municipality : 'Sin municipio',
            'name' => $name !== '' ? $name : $text,
            'google_maps_url' => $url,
        ];
    }

    private function nullable(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function intOrNull(string $value): ?int
    {
        if (preg_match('/\d+/', $value, $matches) !== 1) {
            return null;
        }

        return (int) $matches[0];
    }

    private function yesNoOrNull(string $value): ?int
    {
        $normalized = mb_strtolower(trim($value));
        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, 'sí') || str_starts_with($normalized, 'si')) {
            return 1;
        }

        if (str_starts_with($normalized, 'no')) {
            return 0;
        }

        return null;
    }

    private function protocolStatus(string $value): ?string
    {
        $normalized = mb_strtolower(trim($value));
        if ($normalized === '') {
            return null;
        }

        if (str_contains($normalized, 'proceso')) {
            return 'in_progress';
        }

        if (str_starts_with($normalized, 'no')) {
            return 'no';
        }

        if (str_contains($normalized, 'propio')) {
            return 'yes_own';
        }

        if (str_starts_with($normalized, 'sí') || str_starts_with($normalized, 'si')) {
            return 'yes_external';
        }

        return 'unknown';
    }
}
