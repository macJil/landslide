# SmartSlope

## Current Status

Current Phase:
Academic Website MVP

Current Semester Goal:
Complete the working web application.

## Study Area

Initial Area:
Barangay Irisan, Baguio City.

Expansion:
Future phase.

## Current Technology

Backend:
PHP

Database:
MySQL

Frontend:
HTML / CSS / JavaScript

Additional Course Technologies:
- OOP PHP
- PDO/MySQLi
- jQuery
- AJAX
- JSON
- API integration

## Current Features

- [x] Registration and login with server-assigned user roles
- [x] Resident view for latest active reading by location
- [x] Admin location and reading management with archival
- [x] Resident reports and admin review
- [x] PDO CRUD with prepared statements
- [x] JSON endpoint at `GET /api/readings.php?location_id={id}`
- [x] jQuery AJAX refresh of the selected location reading
- [x] CSRF checks, output escaping, password hashing, and role guards
- [ ] Testing
- [ ] Documentation

## Current Data Sources

Readings are entered by an administrator with a source name and optional source URL. An external provider and automatic import are not configured yet.

## Current API

`GET /api/readings.php?location_id={id}` returns the latest non-archived reading for an active location in Barangay Irisan. It returns JSON and excludes inactive or archived records.

## Current Analysis

`app/RiskAnalyzer.php` applies provisional rainfall-only thresholds. For 1h / 24h / 72h rainfall in mm: high at 50 / 100 / 150; medium at 25 / 50 / 100; normal at 10 / 25 / 50; lower values are low. The highest matching level wins. These classroom thresholds are not official warning criteria and need validation before any public safety use.

## Future Features

### Phase 2
Sensors

### Phase 3
Additional Geographic Areas

### Phase 4
Commercial / Startup Features

## Current Problems

None documented.

## Current Task

Implement and test the academic MVP location, reading, report, and risk-analysis flow.

## Next Task

[NEXT TASK]

## Important Decisions

Document major project decisions here.

- Keep the first release limited to Barangay Irisan.
- Archive locations and readings instead of deleting records with history.
- Risk levels are prototype indicators based only on rainfall; they are not official alerts.
- Herd and DBngin MySQL are local testing tools; production connection settings remain separate.
- For an existing database, run `db_migration_archive_readings.sql` once. For a fresh database, import the updated `db.sql` instead; do not run both paths.
- `db_test_seed.sql` adds a clearly labeled synthetic location and reading for local testing only.
