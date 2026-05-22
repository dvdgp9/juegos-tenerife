<?php

declare(strict_types=1);

namespace JuegosTenerife\Services\Import;

use DateTimeInterface;
use OpenSpout\Reader\XLSX\Reader;
use RuntimeException;

final class ExcelPreviewService
{
    private const EXPECTED_HEADERS = [
        'Tipo Entidad',
        'Modalidad1',
        'Modalidad2',
        'Modalidad3',
        'Modalidad4',
        'Nombre Entidad',
        'Domicilio',
        'Localidad',
        'Municipio',
        'Código Postal',
        'Teléfono1',
        'Teléfono2',
        'Email1',
        'Email2',
        'Web',
        'Facebook',
        'Instagram',
        'X',
        'TikTok',
        'Youtube',
        'Persona Contacto',
        'Cargo Contacto',
        'Teléfono1',
        'Teléfono2',
        'Email',
        'Directiva',
        'Miembros Directiva',
        'Directivos/Hombres',
        'Directivas/Mujeres',
        'Asambleas Anuales',
        'Socios/as',
        'Número Total Socios/as',
        'Socios/Hombres',
        'Socias/Mujeres',
        'Equipos',
        'Equipos por Género',
        'Equipos por Edad',
        'Total Deportistas/Practicantes',
        'Mujeres/Niñas',
        'Hombres/Niños',
        'Edades: De 0 a 5 años',
        'Edades: De 6 a 11 años',
        'Edades: De 12 a 17 años',
        'Edades: De 18 a 29 años',
        'Edades: De 30 a 45 años',
        'Edades: De 46 a 59 años',
        'Edades: 60 años y más',
        'Instalaciones1',
        'Instalaciones2',
        'Instalaciones3',
        'Instalaciones4',
        'Instalaciones5',
        'Instalaciones6',
        'Instalaciones7',
        'Instalaciones8',
        'Instalaciones9',
        'Entrenamientos/Prácticas',
        'Días',
        'Horarios',
        'Breve Historia',
        'Principios Corporativos',
        'Valores Deportivos',
        'Protocolo Igualdad',
        'Protocolo Violencia',
        'LOPIVI',
        'Necesidades Educativas',
        'Discapacidad',
        'Educar Entrenando',
    ];

    /**
     * @return array<string, mixed>
     */
    public function preview(string $filePath, int $sampleLimit = 8): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException('No se encontró el archivo Excel.');
        }

        $reader = new Reader();
        $reader->open($filePath);

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $headers = [];
                $sampleRows = [];
                $records = 0;
                $warnings = [];
                $modalities = [];
                $municipalities = [];
                $mapsCount = 0;
                $missingRequired = [];
                $duplicateHeaders = [];

                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $values = $this->normalizeRow($row->toArray());

                    if ($this->isEmptyRow($values)) {
                        continue;
                    }

                    if ($headers === []) {
                        $headers = $values;
                        $duplicateHeaders = $this->findDuplicateHeaders($headers);
                        continue;
                    }

                    $records++;
                    $rowMap = $this->mapRow($headers, $values);
                    $rowWarnings = $this->validateRow($rowMap, $rowIndex);

                    foreach ($rowWarnings as $warning) {
                        $warnings[] = $warning;
                    }

                    $municipality = $rowMap['Municipio'] ?? '';
                    if ($municipality !== '') {
                        $municipalities[$municipality] = ($municipalities[$municipality] ?? 0) + 1;
                    }

                    foreach (['Modalidad1', 'Modalidad2', 'Modalidad3', 'Modalidad4'] as $field) {
                        $modality = $rowMap[$field] ?? '';
                        if ($modality !== '') {
                            $modalities[$modality] = ($modalities[$modality] ?? 0) + 1;
                        }
                    }

                    foreach ($rowMap as $field => $value) {
                        if (str_starts_with($field, 'Instalaciones') && preg_match_all('~https?://\\S+~', $value) > 0) {
                            $mapsCount++;
                        }
                    }

                    foreach (['Tipo Entidad', 'Nombre Entidad', 'Municipio', 'Modalidad1', 'Domicilio', 'Código Postal'] as $required) {
                        if (($rowMap[$required] ?? '') === '') {
                            $missingRequired[$required][] = $rowIndex;
                        }
                    }

                    if (count($sampleRows) < $sampleLimit) {
                        $sampleRows[] = [
                            'row' => $rowIndex,
                            'type' => $rowMap['Tipo Entidad'] ?? '',
                            'name' => $rowMap['Nombre Entidad'] ?? '',
                            'municipality' => $rowMap['Municipio'] ?? '',
                            'modalities' => array_values(array_filter([
                                $rowMap['Modalidad1'] ?? '',
                                $rowMap['Modalidad2'] ?? '',
                                $rowMap['Modalidad3'] ?? '',
                                $rowMap['Modalidad4'] ?? '',
                            ])),
                        ];
                    }
                }

                if ($headers === []) {
                    throw new RuntimeException('La primera hoja no contiene cabeceras.');
                }

                $missingHeaders = $this->missingHeaders($headers);
                foreach ($missingHeaders as $missingHeader) {
                    $warnings[] = 'Falta la columna esperada: ' . $missingHeader;
                }

                if ($duplicateHeaders !== []) {
                    $warnings[] = 'Hay cabeceras duplicadas: ' . implode(', ', array_keys($duplicateHeaders)) . '. Se mapearán por posición.';
                }

                if ($mapsCount > 0) {
                    $warnings[] = 'Se han detectado enlaces de Google Maps, pero no coordenadas latitud/longitud.';
                }

                return [
                    'sheet' => $sheet->getName(),
                    'headers' => $headers,
                    'records' => $records,
                    'sampleRows' => $sampleRows,
                    'warnings' => array_values(array_unique($warnings)),
                    'duplicateHeaders' => $duplicateHeaders,
                    'missingHeaders' => $missingHeaders,
                    'modalities' => $modalities,
                    'municipalities' => $municipalities,
                    'mapsCount' => $mapsCount,
                    'missingRequired' => $missingRequired,
                ];
            }
        } finally {
            $reader->close();
        }

        throw new RuntimeException('El Excel no contiene hojas.');
    }

    /**
     * @param array<int, mixed> $values
     * @return array<int, string>
     */
    public function normalizeRow(array $values): array
    {
        return array_map(static function (mixed $value): string {
            if ($value instanceof DateTimeInterface) {
                return $value->format('Y-m-d');
            }

            return trim((string) $value);
        }, $values);
    }

    /**
     * @param array<int, string> $values
     */
    public function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if ($value !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, string> $headers
     * @return array<string, array<int, int>>
     */
    public function findDuplicateHeaders(array $headers): array
    {
        $positions = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $positions[$header][] = $index + 1;
        }

        return array_filter($positions, static fn (array $items): bool => count($items) > 1);
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, string> $values
     * @return array<string, string>
     */
    public function mapRow(array $headers, array $values): array
    {
        $row = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $key = $header;
            if (isset($row[$key])) {
                $key = $header . ' #' . ($index + 1);
            }

            $row[$key] = $values[$index] ?? '';
        }

        return $row;
    }

    /**
     * @param array<string, string> $row
     * @return array<int, string>
     */
    public function validateRow(array $row, int $rowIndex): array
    {
        $warnings = [];

        if (($row['Nombre Entidad'] ?? '') === '') {
            $warnings[] = 'Fila ' . $rowIndex . ': falta Nombre Entidad.';
        }

        if (($row['Municipio'] ?? '') === 'Agüimes') {
            $warnings[] = 'Fila ' . $rowIndex . ': municipio fuera de Tenerife detectado.';
        }

        return $warnings;
    }

    /**
     * @param array<int, string> $headers
     * @return array<int, string>
     */
    public function missingHeaders(array $headers): array
    {
        $missing = [];

        foreach (self::EXPECTED_HEADERS as $expectedHeader) {
            if (!in_array($expectedHeader, $headers, true)) {
                $missing[] = $expectedHeader;
            }
        }

        return $missing;
    }
}
