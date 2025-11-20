-- ============================================================================
-- SQL QUERIES PARA BUSCAR Y REEMPLAZAR EN PREGUNTAS DE MOODLE
-- ============================================================================
-- IMPORTANTE: Siempre haz un backup antes de ejecutar UPDATE/DELETE
--
-- mysqldump -u usuario -p nombre_bd > backup_$(date +%Y%m%d).sql
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. BÚSQUEDAS (SELECT) - SEGURAS
-- ----------------------------------------------------------------------------

-- Buscar en TEXTO DE PREGUNTAS
SELECT
    q.id AS pregunta_id,
    q.name AS nombre_pregunta,
    q.questiontext AS texto,
    q.qtype AS tipo_pregunta,
    c.id AS curso_id,
    c.fullname AS nombre_curso,
    quiz.id AS quiz_id,
    quiz.name AS nombre_quiz
FROM mdl_question q
JOIN mdl_question_versions qv ON qv.questionid = q.id
JOIN mdl_question_bank_entries qbe ON qbe.id = qv.questionbankentryid
JOIN mdl_context ctx ON ctx.id = qbe.questioncategoryid
LEFT JOIN mdl_course c ON c.id = ctx.instanceid
LEFT JOIN mdl_quiz_slots qs ON qs.questionid = q.id
LEFT JOIN mdl_quiz quiz ON quiz.id = qs.quizid
WHERE q.questiontext LIKE '%TEXTO_A_BUSCAR%'
ORDER BY c.fullname, quiz.name, q.name;


-- Buscar en RETROALIMENTACIÓN GENERAL
SELECT
    q.id AS pregunta_id,
    q.name AS nombre_pregunta,
    q.generalfeedback AS retroalimentacion,
    q.qtype AS tipo_pregunta,
    c.fullname AS nombre_curso
FROM mdl_question q
JOIN mdl_question_versions qv ON qv.questionid = q.id
JOIN mdl_question_bank_entries qbe ON qbe.id = qv.questionbankentryid
JOIN mdl_context ctx ON ctx.id = qbe.questioncategoryid
LEFT JOIN mdl_course c ON c.id = ctx.instanceid
WHERE q.generalfeedback LIKE '%TEXTO_A_BUSCAR%'
AND q.generalfeedback != '';


-- Buscar en RESPUESTAS
SELECT
    qa.id AS respuesta_id,
    qa.answer AS respuesta,
    q.id AS pregunta_id,
    q.name AS nombre_pregunta,
    q.qtype AS tipo_pregunta,
    c.fullname AS nombre_curso,
    quiz.name AS nombre_quiz
FROM mdl_question_answers qa
JOIN mdl_question q ON q.id = qa.question
JOIN mdl_question_versions qv ON qv.questionid = q.id
JOIN mdl_question_bank_entries qbe ON qbe.id = qv.questionbankentryid
JOIN mdl_context ctx ON ctx.id = qbe.questioncategoryid
LEFT JOIN mdl_course c ON c.id = ctx.instanceid
LEFT JOIN mdl_quiz_slots qs ON qs.questionid = q.id
LEFT JOIN mdl_quiz quiz ON quiz.id = qs.quizid
WHERE qa.answer LIKE '%TEXTO_A_BUSCAR%'
ORDER BY c.fullname, q.name;


-- Buscar en FEEDBACK DE RESPUESTAS
SELECT
    qa.id AS respuesta_id,
    qa.feedback AS feedback,
    q.name AS nombre_pregunta,
    c.fullname AS nombre_curso
FROM mdl_question_answers qa
JOIN mdl_question q ON q.id = qa.question
JOIN mdl_question_versions qv ON qv.questionid = q.id
JOIN mdl_question_bank_entries qbe ON qbe.id = qv.questionbankentryid
JOIN mdl_context ctx ON ctx.id = qbe.questioncategoryid
LEFT JOIN mdl_course c ON c.id = ctx.instanceid
WHERE qa.feedback LIKE '%TEXTO_A_BUSCAR%'
AND qa.feedback != '';


-- Buscar en un CURSO ESPECÍFICO
SELECT
    q.id,
    q.name,
    q.questiontext,
    c.fullname AS curso
FROM mdl_question q
JOIN mdl_question_versions qv ON qv.questionid = q.id
JOIN mdl_question_bank_entries qbe ON qbe.id = qv.questionbankentryid
JOIN mdl_context ctx ON ctx.id = qbe.questioncategoryid
JOIN mdl_course c ON c.id = ctx.instanceid
WHERE q.questiontext LIKE '%TEXTO_A_BUSCAR%'
AND c.id = 123; -- Reemplaza 123 con el ID del curso


-- Contar ocurrencias por tipo de pregunta
SELECT
    q.qtype AS tipo_pregunta,
    COUNT(*) AS cantidad
FROM mdl_question q
WHERE q.questiontext LIKE '%TEXTO_A_BUSCAR%'
GROUP BY q.qtype
ORDER BY cantidad DESC;


-- Buscar SENSIBLE A MAYÚSCULAS (MySQL)
SELECT * FROM mdl_question
WHERE questiontext LIKE BINARY '%TextoExacto%';


-- ----------------------------------------------------------------------------
-- 2. REEMPLAZOS (UPDATE) - ¡¡¡USAR CON PRECAUCIÓN!!!
-- ----------------------------------------------------------------------------

-- ⚠️ SIEMPRE HACER BACKUP ANTES
-- ⚠️ PROBAR PRIMERO CON SELECT
-- ⚠️ EJECUTAR EN TRANSACCIÓN

-- INICIO DE TRANSACCIÓN
START TRANSACTION;

-- Reemplazar en TEXTO DE PREGUNTAS
UPDATE mdl_question
SET questiontext = REPLACE(questiontext, 'texto_viejo', 'texto_nuevo'),
    timemodified = UNIX_TIMESTAMP()
WHERE questiontext LIKE '%texto_viejo%';

-- Ver cuántas filas se afectarían (antes del UPDATE)
SELECT COUNT(*) AS filas_afectadas
FROM mdl_question
WHERE questiontext LIKE '%texto_viejo%';

-- Reemplazar en RETROALIMENTACIÓN
UPDATE mdl_question
SET generalfeedback = REPLACE(generalfeedback, 'texto_viejo', 'texto_nuevo'),
    timemodified = UNIX_TIMESTAMP()
WHERE generalfeedback LIKE '%texto_viejo%';

-- Reemplazar en RESPUESTAS
UPDATE mdl_question_answers
SET answer = REPLACE(answer, 'texto_viejo', 'texto_nuevo')
WHERE answer LIKE '%texto_viejo%';

-- Reemplazar en FEEDBACK DE RESPUESTAS
UPDATE mdl_question_answers
SET feedback = REPLACE(feedback, 'texto_viejo', 'texto_nuevo')
WHERE feedback LIKE '%texto_viejo%';

-- Si todo está bien, confirmar:
COMMIT;

-- Si algo salió mal, revertir:
-- ROLLBACK;


-- Reemplazar solo en un CURSO ESPECÍFICO
UPDATE mdl_question q
JOIN mdl_question_versions qv ON qv.questionid = q.id
JOIN mdl_question_bank_entries qbe ON qbe.id = qv.questionbankentryid
JOIN mdl_context ctx ON ctx.id = qbe.questioncategoryid
JOIN mdl_course c ON c.id = ctx.instanceid
SET q.questiontext = REPLACE(q.questiontext, 'texto_viejo', 'texto_nuevo'),
    q.timemodified = UNIX_TIMESTAMP()
WHERE q.questiontext LIKE '%texto_viejo%'
AND c.id = 123; -- ID del curso


-- Reemplazar solo en un QUIZ ESPECÍFICO
UPDATE mdl_question q
JOIN mdl_quiz_slots qs ON qs.questionid = q.id
SET q.questiontext = REPLACE(q.questiontext, 'texto_viejo', 'texto_nuevo'),
    q.timemodified = UNIX_TIMESTAMP()
WHERE q.questiontext LIKE '%texto_viejo%'
AND qs.quizid = 456; -- ID del quiz


-- Reemplazar SENSIBLE A MAYÚSCULAS (requiere función personalizada)
-- Esta es más compleja, mejor usar el plugin


-- ----------------------------------------------------------------------------
-- 3. LIMPIAR CACHÉS (IMPORTANTE DESPUÉS DE CAMBIOS)
-- ----------------------------------------------------------------------------

-- Después de hacer cambios manuales, DEBES limpiar cachés de Moodle
-- Ejecutar desde la terminal del servidor:

-- php admin/cli/purge_caches.php

-- O desde la interfaz:
-- Administración del sitio → Desarrollo → Purgar todas las cachés


-- ----------------------------------------------------------------------------
-- 4. CONSULTAS ÚTILES ADICIONALES
-- ----------------------------------------------------------------------------

-- Ver todas las preguntas de un cuestionario específico
SELECT
    qs.slot,
    q.id,
    q.name,
    q.qtype,
    LEFT(q.questiontext, 100) AS preview
FROM mdl_quiz_slots qs
JOIN mdl_question q ON q.id = qs.questionid
WHERE qs.quizid = 123 -- ID del quiz
ORDER BY qs.slot;


-- Ver preguntas modificadas recientemente
SELECT
    q.id,
    q.name,
    FROM_UNIXTIME(q.timemodified) AS fecha_modificacion,
    c.fullname AS curso
FROM mdl_question q
JOIN mdl_question_versions qv ON qv.questionid = q.id
JOIN mdl_question_bank_entries qbe ON qbe.id = qv.questionbankentryid
JOIN mdl_context ctx ON ctx.id = qbe.questioncategoryid
LEFT JOIN mdl_course c ON c.id = ctx.instanceid
WHERE q.timemodified > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY))
ORDER BY q.timemodified DESC;


-- Ver estadísticas de preguntas por curso
SELECT
    c.fullname AS curso,
    COUNT(DISTINCT q.id) AS total_preguntas,
    COUNT(DISTINCT CASE WHEN q.qtype = 'multichoice' THEN q.id END) AS opcion_multiple,
    COUNT(DISTINCT CASE WHEN q.qtype = 'truefalse' THEN q.id END) AS verdadero_falso,
    COUNT(DISTINCT CASE WHEN q.qtype = 'essay' THEN q.id END) AS ensayo,
    COUNT(DISTINCT CASE WHEN q.qtype = 'shortanswer' THEN q.id END) AS respuesta_corta
FROM mdl_question q
JOIN mdl_question_versions qv ON qv.questionid = q.id
JOIN mdl_question_bank_entries qbe ON qbe.id = qv.questionbankentryid
JOIN mdl_context ctx ON ctx.id = qbe.questioncategoryid
JOIN mdl_course c ON c.id = ctx.instanceid
WHERE c.id > 1
GROUP BY c.id, c.fullname
ORDER BY total_preguntas DESC;


-- ----------------------------------------------------------------------------
-- 5. BACKUP Y RESTAURACIÓN
-- ----------------------------------------------------------------------------

-- CREAR BACKUP de tablas de preguntas
-- Ejecutar desde terminal:

-- mysqldump -u usuario -p nombre_bd \
--   mdl_question \
--   mdl_question_answers \
--   mdl_question_versions \
--   mdl_question_bank_entries \
--   > backup_preguntas_$(date +%Y%m%d_%H%M%S).sql

-- RESTAURAR desde backup:
-- mysql -u usuario -p nombre_bd < backup_preguntas_20251117_120000.sql


-- ----------------------------------------------------------------------------
-- 6. VERIFICACIÓN DESPUÉS DE CAMBIOS
-- ----------------------------------------------------------------------------

-- Verificar que los cambios se aplicaron
SELECT
    q.id,
    q.name,
    q.questiontext,
    FROM_UNIXTIME(q.timemodified) AS ultima_modificacion
FROM mdl_question q
WHERE q.questiontext LIKE '%texto_nuevo%'
AND q.timemodified > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR));


-- Verificar integridad de preguntas (no deben estar vacías)
SELECT COUNT(*) AS preguntas_sin_texto
FROM mdl_question
WHERE questiontext IS NULL OR questiontext = '';


-- ============================================================================
-- NOTAS IMPORTANTES:
-- ============================================================================
--
-- 1. Prefijo de tablas: Este script usa 'mdl_' como prefijo.
--    Ajusta según tu configuración (puede ser diferente).
--
-- 2. IDs: Los IDs de ejemplo (123, 456) son solo ilustrativos.
--    Usa los IDs reales de tu base de datos.
--
-- 3. HTML: Ten en cuenta que Moodle almacena texto con HTML.
--    Buscar "texto" puede no encontrar "<p>texto</p>".
--
-- 4. Backups: SIEMPRE haz backup antes de UPDATE/DELETE.
--
-- 5. Permisos: Asegúrate de tener permisos suficientes en la BD.
--
-- 6. Cachés: SIEMPRE limpia cachés después de cambios manuales.
--
-- 7. Testing: Prueba primero en un entorno de desarrollo.
--
-- 8. Plugin recomendado: Para cambios seguros, usa el plugin
--    Question Search & Replace que incluye verificaciones y rollback.
--
-- ============================================================================
