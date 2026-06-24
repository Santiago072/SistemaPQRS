#!/bin/bash
# Script de despliegue automático - Sistema PQRS

echo "Iniciando despliegue automático..."

# 1. Asegurar permisos para evitar conflictos con archivos creados por Docker
echo "[1/4] Ajustando permisos locales..."
sudo chown -R $USER:$USER .

# 2. Obtener los últimos cambios de GitHub
echo "[2/4] Obteniendo cambios de GitHub..."
git fetch origin

# 3. Forzar sincronización exacta con master
echo "[3/4] Sincronizando con la rama master..."
git reset --hard origin/master

# 4. Reconstruir y levantar contenedores
echo "[4/4] Reconstruyendo y levantando contenedores Docker..."
docker compose up -d --build

echo ""
echo "✅ Despliegue completado exitosamente."
