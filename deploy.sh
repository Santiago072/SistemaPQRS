#!/bin/bash
# ============================================================
# deploy.sh — Script oficial de actualización Sistema PQRS
# Usar en el VPS: chmod +x deploy.sh && ./deploy.sh
# ============================================================

set -e  # Detiene el script si cualquier comando falla

echo ""
echo "=========================================="
echo " 🚀 Desplegando Sistema PQRS en el VPS..."
echo "=========================================="
echo ""

# Paso 1: Ajustar permisos
echo "🔒 Ajustando permisos..."
sudo chown -R $USER:$USER .

# Paso 2: Obtener cambios de GitHub
echo "⬇️  Obteniendo código desde GitHub (rama main)..."
git fetch origin
git reset --hard origin/main

# Paso 3: Copiar config de Nginx y habilitar el sitio
echo "🔧 Actualizando configuración de Nginx..."
sudo cp nginx/pqrs.conf /etc/nginx/sites-available/pqrs.conf
sudo ln -sf /etc/nginx/sites-available/pqrs.conf /etc/nginx/sites-enabled/pqrs.conf
sudo nginx -t && sudo systemctl reload nginx

# Paso 4: Reconstruir y levantar contenedores
echo "🐳 Reconstruyendo y levantando contenedores Docker..."
sudo docker compose up -d --build

# Paso 5: Limpiar imágenes obsoletas
echo "🧹 Limpiando imágenes antiguas..."
sudo docker image prune -f

# Paso 6: Mostrar estado
echo ""
echo "📊 Estado de los contenedores:"
sudo docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo "=========================================="
echo " ✅ ¡Despliegue completado con éxito!"
echo "    🌐 App: https://sistemapqrs.slscode.online"
echo "=========================================="
echo ""
