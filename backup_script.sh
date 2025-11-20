#!/bin/bash

################################################################################
# Script de Backup para Base de Datos de Moodle
# Uso: ./backup_script.sh
################################################################################

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}======================================"
echo "  Moodle Database Backup Script"
echo -e "======================================${NC}\n"

# Configuración - EDITAR SEGÚN TU INSTALACIÓN
DB_USER="root"
DB_NAME="moodle"
BACKUP_DIR="/var/backups/moodle"
DATE=$(date +%Y%m%d_%H%M%S)

# Crear directorio de backup si no existe
mkdir -p "$BACKUP_DIR"

# Solicitar contraseña de MySQL
echo -e "${YELLOW}Ingresa la contraseña de MySQL para el usuario '$DB_USER':${NC}"
read -s DB_PASS

echo -e "\n${GREEN}Creando backup de la base de datos completa...${NC}"
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/moodle_full_$DATE.sql"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup completo creado: moodle_full_$DATE.sql${NC}"
else
    echo -e "${RED}✗ Error al crear backup completo${NC}"
    exit 1
fi

echo -e "\n${GREEN}Creando backup solo de tablas de preguntas...${NC}"
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    mdl_question \
    mdl_question_answers \
    mdl_question_versions \
    mdl_question_bank_entries \
    mdl_question_categories \
    mdl_quiz \
    mdl_quiz_slots \
    > "$BACKUP_DIR/moodle_questions_$DATE.sql"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup de preguntas creado: moodle_questions_$DATE.sql${NC}"
else
    echo -e "${RED}✗ Error al crear backup de preguntas${NC}"
fi

# Comprimir backups
echo -e "\n${GREEN}Comprimiendo backups...${NC}"
gzip "$BACKUP_DIR/moodle_full_$DATE.sql"
gzip "$BACKUP_DIR/moodle_questions_$DATE.sql"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backups comprimidos exitosamente${NC}"
fi

# Mostrar tamaños
echo -e "\n${GREEN}Tamaño de los backups:${NC}"
ls -lh "$BACKUP_DIR" | grep "$DATE"

# Limpiar backups antiguos (más de 30 días)
echo -e "\n${YELLOW}Limpiando backups antiguos (más de 30 días)...${NC}"
find "$BACKUP_DIR" -name "moodle_*.sql.gz" -mtime +30 -delete
echo -e "${GREEN}✓ Limpieza completada${NC}"

# Resumen
echo -e "\n${GREEN}======================================"
echo "  Backup Completado"
echo "======================================${NC}"
echo -e "Ubicación: ${YELLOW}$BACKUP_DIR${NC}"
echo -e "Fecha: ${YELLOW}$DATE${NC}"
echo -e "\n${GREEN}Archivos creados:${NC}"
echo "  - moodle_full_$DATE.sql.gz (completo)"
echo "  - moodle_questions_$DATE.sql.gz (solo preguntas)"

echo -e "\n${YELLOW}Para restaurar:${NC}"
echo "  gunzip $BACKUP_DIR/moodle_full_$DATE.sql.gz"
echo "  mysql -u $DB_USER -p $DB_NAME < $BACKUP_DIR/moodle_full_$DATE.sql"

echo -e "\n${GREEN}¡Backup completado exitosamente!${NC}\n"
