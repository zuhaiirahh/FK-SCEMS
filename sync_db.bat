@echo off
echo Importing fkscems database into XAMPP...
C:\Xampps\mysql\bin\mysql.exe -u root -P 3307 fkscems < fkscems.sql
echo Done! Your database is now synced.
pause