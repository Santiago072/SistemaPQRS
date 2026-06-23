#!/bin/bash
echo "🚀 Actualizando el código desde GitHub..."
git pull origin master

echo "📦 Reconstruyendo los contenedores con el nuevo código..."
docker compose up -d --build

echo "✅ ¡Actualización completada con éxito!"
