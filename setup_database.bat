@echo off
echo Creating database...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS restaurant_4to9;"
echo Database created successfully.
echo Importing database schema...
"C:\xampp\mysql\bin\mysql.exe" -u root restaurant_4to9 < database\restaurant_4to9.sql
echo Database import completed.
pause