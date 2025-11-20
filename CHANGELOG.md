# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.1.1] - 2025-11-20

### Crítico - Mejora en Eliminación
- 🔧 **Ahora limpia ~25 tablas relacionadas** (antes solo ~10)
- ✅ **Soporte completo para Moodle 3.9+ y 4.x**
- 🛡️ **Verificación de existencia de tablas** antes de intentar eliminar
- 🧹 **Limpieza de intentos de usuarios** (question_attempts, question_attempt_steps)
- 📦 **Limpieza de banco de preguntas** (question_versions, question_references)

### Nuevas Herramientas
- 🔍 **delete-diagnostico.php**: Script para diagnosticar problemas de eliminación
- 📖 **SOLUCION-ERRORES-ELIMINACION.md**: Guía completa de troubleshooting

### Tablas Adicionales Incluidas
- question_gapselect, question_ddwtos, question_ddmarker, question_ddimageortext
- qtype_*_options (para Moodle 4.x)
- question_attempts, question_attempt_steps, question_attempt_step_data
- question_references, question_versions, question_set_references

### Corregido
- 🐛 **"Error escribiendo a la base de datos"** al eliminar preguntas
- 🐛 Fallos por tablas que no existen en versiones antiguas de Moodle
- 🐛 Fallos por foreign keys en tablas de intentos de usuarios

## [1.1.0] - 2025-11-20

### Mejorado
- 🔒 Sistema de eliminación de preguntas ahora usa transacciones con rollback automático
- ✨ Mejor manejo de errores en eliminación de preguntas
- ✨ Eliminación de IDs duplicados en selección múltiple
- 📝 Mensajes de error más descriptivos y detallados
- 🛡️ Validación mejorada de IDs de preguntas antes de eliminar
- 📄 Títulos de página correctos en todas las vistas

### Seguridad
- 🔒 Transacciones garantizan integridad de datos (todo o nada)
- 🔒 Rollback automático si cualquier eliminación falla

### Corregido
- 🐛 Error al intentar eliminar preguntas que ya no existen
- 🐛 Problema con IDs duplicados en selecciones múltiples
- 🐛 Falta de rollback en caso de error durante eliminación

## [1.0.0] - 2025-11-17

### Añadido
- ✨ Funcionalidad de búsqueda en preguntas de cuestionarios
- ✨ Búsqueda en texto de preguntas, retroalimentación general y respuestas
- ✨ Filtrado por curso específico o todos los cursos
- ✨ Opción de búsqueda sensible a mayúsculas/minúsculas
- ✨ Opción de coincidencia exacta
- ✨ Funcionalidad de reemplazo masivo y selectivo
- ✨ Vista previa de resultados con resaltado
- ✨ Interfaz bilingüe (Español/Inglés)
- ✨ Transacciones seguras con rollback automático
- ✨ Limpieza automática de caché después de reemplazos
- ✨ Sistema de permisos y capabilities
- 📚 Documentación completa (README, INSTALL)
- 📚 Ejemplos de SQL para búsquedas directas

### Seguridad
- 🔒 Protección CSRF con tokens de sesión
- 🔒 Uso de API de Moodle para acceso seguro a BD
- 🔒 Permisos restringidos a administradores
- 🔒 Validación de entrada de usuario
- 🔒 Transacciones con rollback en caso de error

### Documentación
- 📖 README completo con ejemplos de uso
- 📖 Guía de instalación detallada
- 📖 Ejemplos de consultas SQL
- 📖 Solución de problemas comunes
- 📖 Mejores prácticas y advertencias

## [Futuras mejoras planificadas]

### Por hacer
- [ ] Exportar resultados a CSV/Excel
- [ ] Búsqueda en más campos (hints, explicaciones)
- [ ] Previsualización de cambios antes de aplicar
- [ ] Historial de cambios/auditoría
- [ ] Soporte para expresiones regulares
- [ ] Búsqueda y reemplazo en actividades además de cuestionarios
- [ ] API REST para integración con otros sistemas
- [ ] Programación de búsquedas automáticas
- [ ] Notificaciones a profesores de cursos afectados
- [ ] Modo de prueba/simulación sin aplicar cambios

### Considerando
- [ ] Interfaz mejorada con Ajax/React
- [ ] Búsqueda en contenido de Moodle (recursos, páginas, etc.)
- [ ] Integración con herramientas de traducción
- [ ] Soporte para búsqueda en múltiples idiomas
- [ ] Dashboard con estadísticas de uso

---

**Formato de versiones:**
- MAJOR.MINOR.PATCH (ej: 1.0.0)
- MAJOR: Cambios incompatibles con versiones anteriores
- MINOR: Nueva funcionalidad compatible con versiones anteriores
- PATCH: Correcciones de bugs compatibles con versiones anteriores
