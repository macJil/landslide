--create database

CREATE DATABASE IF NOT EXISTS smartslope_mvp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

EXIT;
-- import slq
mysql -u root -p smartslope_mvp < db.sql  