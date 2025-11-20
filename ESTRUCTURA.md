# Estructura del Plugin

Este documento describe la estructura de archivos y directorios del plugin.

## 📁 Estructura de Directorios

```
questionsearch/
├── db/
│   └── access.php                 # Definición de permisos y capabilities
├── lang/
│   ├── en/
│   │   └── tool_questionsearch.php   # Strings en inglés
│   └── es/
│       └── tool_questionsearch.php   # Strings en español
├── .same/
│   └── todos.md                   # Lista de tareas (desarrollo)
├── version.php                    # Información del plugin
├── settings.php                   # Registro en menú de administración
├── index.php                      # Página principal - formulario de búsqueda
├── search.php                     # Procesamiento de búsqueda y resultados
├── replace.php                    # Procesamiento de reemplazos
├── README.md                      # Documentación principal
├── INSTALL.md                     # Guía de instalación
├── FAQ.md                         # Preguntas frecuentes
├── CHANGELOG.md                   # Historial de versiones
├── ESTRUCTURA.md                  # Este archivo
├── sql_examples.sql               # Ejemplos de consultas SQL directas
├── backup_script.sh               # Script de backup automatizado
└── config_example.php             # Ejemplos de configuración
```

## 📄 Descripción de Archivos

### Archivos Core del Plugin

#### `version.php`
Define la información del plugin:
- Componente (`tool_questionsearch`)
- Versión actual (`2025111700`)
- Requisitos de Moodle (3.9+)
- Nivel de madurez (STABLE)
- Número de release (v1.0.0)

#### `settings.php`
Registra el plugin en el menú de administración de Moodle:
- Crea entrada en "Herramientas de administración"
- Define URL de acceso
- Establece permisos requeridos

#### `db/access.php`
Define capabilities (permisos):
- `tool/questionsearch:use` - Permiso para usar el plugin
- Por defecto asignado a administradores (manager archetype)
- Nivel de contexto: SYSTEM

### Archivos de Interfaz

#### `index.php`
Página principal con formulario de búsqueda:
- **Campos:**
  - Término de búsqueda (requerido)
  - Término de reemplazo (opcional)
  - Selector de curso
  - Checkboxes para áreas de búsqueda
  - Opciones de búsqueda (case sensitive, exact match)

- **Validaciones:**
  - Requiere capability `tool/questionsearch:use`
  - Protección CSRF (sesskey)

- **Estilo:**
  - CSS inline para formulario responsive
  - Diseño limpio y profesional

#### `search.php`
Procesamiento de búsqueda y visualización de resultados:
- **Funcionalidad:**
  - Construye queries SQL dinámicas según parámetros
  - Busca en: questiontext, generalfeedback, answers
  - Filtra por curso si se especifica
  - Aplica case sensitive y exact match si se requiere

- **Resultados:**
  - Tabla con información detallada
  - Resaltado del término buscado
  - Links a cuestionarios
  - Checkboxes para selección (si hay término de reemplazo)
  - Botones de acción masiva

- **Tablas consultadas:**
  - `mdl_question`
  - `mdl_question_answers`
  - `mdl_question_versions`
  - `mdl_question_bank_entries`
  - `mdl_context`
  - `mdl_course`
  - `mdl_quiz_slots`
  - `mdl_quiz`

#### `replace.php`
Procesamiento de reemplazos:
- **Seguridad:**
  - Requiere sesskey
  - Requiere capability
  - Usa transacciones con rollback

- **Funcionalidad:**
  - Reemplaza en campos seleccionados
  - Actualiza `timemodified`
  - Notifica cambios al banco de preguntas
  - Purga cachés automáticamente

- **Manejo de errores:**
  - Try-catch para excepciones
  - Rollback automático si falla
  - Mensajes de error descriptivos

### Archivos de Idioma

#### `lang/en/tool_questionsearch.php`
Strings en inglés:
- Interfaz completa
- Mensajes de error
- Ayudas y descripciones

#### `lang/es/tool_questionsearch.php`
Strings en español:
- Traducción completa
- Adaptada a terminología de Moodle en español

### Documentación

#### `README.md`
Documentación principal:
- Características
- Requisitos
- Instalación detallada
- Instrucciones de uso
- Ejemplos prácticos
- Consultas SQL directas
- Seguridad y mejores prácticas
- Solución de problemas
- Información de tablas de BD

#### `INSTALL.md`
Guía rápida de instalación:
- Métodos de instalación (manual, web, git)
- Comandos paso a paso
- Configuración de permisos
- Verificación de instalación
- Solución de problemas de instalación

#### `FAQ.md`
Preguntas frecuentes:
- Problemas comunes y soluciones
- Casos de uso específicos
- Configuración avanzada
- Troubleshooting

#### `CHANGELOG.md`
Historial de versiones:
- Versión actual (1.0.0)
- Features implementadas
- Mejoras de seguridad
- Roadmap futuro

#### `ESTRUCTURA.md`
Este archivo - documentación de la estructura

### Utilidades

#### `sql_examples.sql`
Colección de consultas SQL:
- Búsquedas en diferentes campos
- Filtros por curso/quiz
- Reemplazos directos (con advertencias)
- Consultas de verificación
- Estadísticas y análisis
- Ejemplos de backup/restore

#### `backup_script.sh`
Script bash para backups automatizados:
- Backup completo de BD
- Backup solo de tablas de preguntas
- Compresión automática
- Limpieza de backups antiguos
- Colores y output amigable

#### `config_example.php`
Ejemplos y referencias de configuración:
- Configuración de MySQL
- Variables de entorno PHP
- Configuración de Moodle
- Rutas comunes
- Comandos CLI útiles
- Permisos recomendados

## 🔄 Flujo de Ejecución

### Flujo de Búsqueda

```
Usuario → index.php (formulario)
           ↓
       search.php (procesa búsqueda)
           ↓
       Construye SQL queries
           ↓
       Consulta BD (mdl_question, mdl_question_answers, etc.)
           ↓
       Agrupa y formatea resultados
           ↓
       Muestra tabla con resultados
           ↓
       (Opcional) Usuario selecciona items
```

### Flujo de Reemplazo

```
Usuario selecciona items → Confirma acción
                              ↓
                          replace.php
                              ↓
                       Inicia transacción
                              ↓
                     Itera sobre seleccionados
                              ↓
                     Aplica str_replace/str_ireplace
                              ↓
                     Actualiza registros en BD
                              ↓
                     Notifica cambios (question_bank)
                              ↓
                     Commit de transacción
                              ↓
                     Purga cachés
                              ↓
                     Redirect con mensaje de éxito
                              ↓
                  (Si error: Rollback + mensaje de error)
```

## 🗄️ Tablas de Base de Datos Utilizadas

### Lectura (SELECT)
- `mdl_question` - Datos de preguntas
- `mdl_question_answers` - Respuestas de preguntas
- `mdl_question_versions` - Versiones de preguntas
- `mdl_question_bank_entries` - Entradas del banco
- `mdl_context` - Contextos (para relacionar con cursos)
- `mdl_course` - Información de cursos
- `mdl_quiz` - Cuestionarios
- `mdl_quiz_slots` - Relación pregunta-quiz

### Escritura (UPDATE)
- `mdl_question` - Actualiza questiontext, generalfeedback
- `mdl_question_answers` - Actualiza answer, feedback

### No Modificadas (Integridad)
El plugin NO modifica:
- Estructura de tablas
- Relaciones entre tablas
- IDs o claves primarias
- Configuraciones del sistema

## 🔐 Seguridad

### Implementaciones de Seguridad

1. **Autenticación y Autorización**
   - `require_capability()` en todas las páginas
   - Solo administradores por defecto
   - Verificación de contexto del sistema

2. **Protección CSRF**
   - `require_sesskey()` en forms
   - `sesskey_field()` en formularios HTML
   - Validación de token de sesión

3. **Validación de Entrada**
   - `required_param()` para campos obligatorios
   - `optional_param()` para campos opcionales
   - Tipos de parámetros especificados (PARAM_RAW, PARAM_INT, etc.)

4. **Salida Segura**
   - `s()` para sanitizar texto
   - `htmlspecialchars()` para escapar HTML
   - Uso de placeholders en queries SQL

5. **Base de Datos**
   - Uso de API de Moodle ($DB)
   - Preparación de statements (previene SQL injection)
   - Transacciones con rollback

6. **Caché**
   - Limpieza automática post-modificaciones
   - `purge_all_caches()` después de cambios
   - `question_bank::notify_question_edited()`

## 🧪 Testing Recomendado

### Testing Manual

1. **Instalación**
   - Instalar en Moodle limpio
   - Verificar permisos
   - Comprobar aparición en menú

2. **Búsqueda**
   - Buscar con todos los filtros
   - Probar case sensitive
   - Probar exact match
   - Buscar en curso específico
   - Buscar en todas las áreas

3. **Reemplazo**
   - Reemplazar un solo item
   - Reemplazar múltiples items
   - Verificar cambios en BD
   - Verificar visualización en Moodle

4. **Casos Extremos**
   - Búsqueda sin resultados
   - Términos con HTML
   - Términos con caracteres especiales
   - Bases de datos grandes

### Testing Automatizado (Futuro)

- Unit tests con PHPUnit
- Behat tests para UI
- Integration tests con BD de prueba

## 📊 Rendimiento

### Optimizaciones Implementadas

- Uso de LEFT JOIN en lugar de múltiples queries
- Índices existentes de Moodle (id, questionid, etc.)
- Límite de resultados en preview (200 caracteres)
- Transacciones para operaciones batch

### Consideraciones de Rendimiento

- Búsquedas en bases de datos grandes pueden tardar
- Recomendado filtrar por curso en instalaciones grandes
- El uso de LIKE puede ser lento con millones de registros
- Para optimización máxima, usar índices full-text (requiere cambios en BD)

## 🔮 Extensiones Futuras

Ver `CHANGELOG.md` para roadmap completo.

Áreas de mejora:
- Búsqueda con regex
- Export de resultados
- Historial de cambios
- API REST
- Búsqueda en más tipos de actividades
- Interfaz Ajax/React
- Dashboard de estadísticas

## 📞 Contacto y Contribuciones

Para contribuir:
1. Fork del repositorio
2. Crear rama feature
3. Commit con mensajes descriptivos
4. Push y Pull Request

Para reportar bugs:
- Abrir issue con descripción detallada
- Incluir versión de Moodle y PHP
- Adjuntar logs si es posible
