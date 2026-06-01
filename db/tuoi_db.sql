-- 1. Creamos la base de datos de Tuoi
CREATE DATABASE IF NOT EXISTS tuoi_db;
USE tuoi_db;

CREATE USER 
'tuoi_admin2026'@'localhost' 
IDENTIFIED  BY 'Tuoi123$';

GRANT USAGE ON *.* TO 'tuoi_admin2026'@'localhost';

ALTER USER 'tuoi_admin2026'@'localhost' 
REQUIRE NONE 
WITH MAX_QUERIES_PER_HOUR 0 
MAX_CONNECTIONS_PER_HOUR 0 
MAX_UPDATES_PER_HOUR 0 
MAX_USER_CONNECTIONS 0;

GRANT ALL PRIVILEGES ON tuoi_db.* 
TO 'tuoi_admin2026'@'localhost';

FLUSH PRIVILEGES;
