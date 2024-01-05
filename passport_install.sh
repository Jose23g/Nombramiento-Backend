#!/bin/bash

# Ejecutar el comando y almacenar la salida en la variable credentials
credentials="$(php artisan passport:install)"

# Extraer la información necesaria del resultado
grantClient=$(echo $credentials | awk -F'Password grant client created successfully. ' '{print $2}')
clientId=$(echo "$grantClient" | grep -oP 'Client ID: \K[^\n]+' | awk -F' ' '{print $1}')
clientSecret=$(echo "$grantClient" | grep -oP 'Client secret: \K[^\n]+')
ENV_FILE=".env"
# Verificar si el archivo .env existe
if [ -f "$ENV_FILE" ]; then
    # Agregar las nuevas variables al archivo .env
    sed -i.bak "s/^CLIENT_ID=.*/CLIENT_ID=$clientId/" "$ENV_FILE"
    sed -i.bak "s/^CLIENT_SECRET=.*/CLIENT_SECRET=$clientSecret/" "$ENV_FILE"

    echo "Variables agregadas correctamente al archivo .env"
else
    echo "El archivo .env no existe en la ruta especificada."
fi
