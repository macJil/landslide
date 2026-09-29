-- LOCAL TEST DATA ONLY. Do not run this file on a public or production database.
-- The active location is clearly marked TEST ONLY so the report form can be tested.
USE smartslope_mvp;

INSERT INTO locations (
    barangay_id,
    location_name,
    purok_zone,
    susceptibility_class,
    is_active
)
SELECT
    barangay_id,
    'TEST ONLY - report flow location',
    'LOCAL TEST',
    'unknown',
    1
FROM barangays
WHERE barangay_name = 'Barangay Irisan'
  AND city_name = 'Baguio City'
ON DUPLICATE KEY UPDATE is_active = 1;
