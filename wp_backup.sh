#!/bin/bash
 
# VARIABLES
# variable para la fecha 
v_date=`date +%F | tr -s  "-" " " | tr -d " "`
# WordPress usuario db 
wpdb_user="root"
# WordPress password db
wpdb_pass=""
# WordPress nombre dbe
wpdb_dbname="store_wp"
# Email a enviar la notificacion 
wp_email="mendoza.gonzalez51096@gmail.com"
# WordPress ubicacion de instalacion
wp_documentroot="/root/jenni-store/wordpress"
# WordPress ubicacion deonde se guardara el backup 
wp_backup_dir="/home/"
 
# SCRIPT
# Crear directorios
mkdir -p $wp_backup_dir/$v_date/files
mkdir -p $wp_backup_dir/$v_date/database
 
# archivo Backup y base de datos de WordPress 
cp -R $wp_documentroot/* $wp_backup_dir/$v_date/files/
sudo mysqldump -u$wpdb_user -p$wpdb_pass -h localhost $wpdb_dbname > $wp_backup_dir/$v_date/database/wpdatabase.sql
 
# Crear archivo TAR y comprimir con GZIP
cd $wp_backup_dir
tar -zcvf $v_date.tar.gz $v_date/ --remove-files
 
# Eliminar backups antiguos
find $wp_backup_dir -maxdepth 1 -mtime +15 -exec rm -rf "{}" ";" > /dev/null
 
# enviar email de confirmacion
#echo 'Backup for your site has been completed' | mail -s "Wordpress backup successfully completed" $wp_email
