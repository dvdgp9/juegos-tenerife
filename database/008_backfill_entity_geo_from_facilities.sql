UPDATE entities e
SET
    e.latitude = COALESCE(
        e.latitude,
        (
            SELECT f.latitude
            FROM entity_facilities ef
            INNER JOIN facilities f ON f.id = ef.facility_id
            WHERE ef.entity_id = e.id
              AND f.latitude IS NOT NULL
              AND f.longitude IS NOT NULL
              AND f.deleted_at IS NULL
            ORDER BY ef.sort_order ASC, f.id ASC
            LIMIT 1
        )
    ),
    e.longitude = COALESCE(
        e.longitude,
        (
            SELECT f.longitude
            FROM entity_facilities ef
            INNER JOIN facilities f ON f.id = ef.facility_id
            WHERE ef.entity_id = e.id
              AND f.latitude IS NOT NULL
              AND f.longitude IS NOT NULL
              AND f.deleted_at IS NULL
            ORDER BY ef.sort_order ASC, f.id ASC
            LIMIT 1
        )
    ),
    e.geocoding_status = 'resolved'
WHERE e.deleted_at IS NULL
  AND (e.latitude IS NULL OR e.longitude IS NULL OR e.geocoding_status <> 'resolved')
  AND EXISTS (
      SELECT 1
      FROM entity_facilities ef
      INNER JOIN facilities f ON f.id = ef.facility_id
      WHERE ef.entity_id = e.id
        AND f.latitude IS NOT NULL
        AND f.longitude IS NOT NULL
        AND f.deleted_at IS NULL
  );
