#!/bin/bash

# Update MySQL credentials (prevent MySQL warnings about CLI password usage)
CREDS=/root/mysql-credentials.cnf
echo "[client]" > $CREDS
echo "user=$MYSQL_USER" >> $CREDS
echo "password=$MYSQL_PASSWORD" >> $CREDS
chmod 600 $CREDS

#Update Composer to v2
composer self-update --2

# Start apache
apache2-foreground
