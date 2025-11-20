<?php
// Script de diagnóstico para identificar problemas de eliminación
// NO usar en producción - solo para diagnóstico

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('tool_questionsearch');
require_capability('tool/questionsearch:use', context_system::instance());
require_sesskey();

$questionid = required_param('qid', PARAM_INT);

echo $OUTPUT->header();
echo $OUTPUT->heading('Diagnóstico de Pregunta ID: ' . $questionid);

// 1. Verificar que la pregunta existe
echo "<h3>1. Verificando existencia de la pregunta</h3>";
$question = $DB->get_record('question', array('id' => $questionid));
if ($question) {
    echo "<p style='color: green;'>✓ Pregunta existe</p>";
    echo "<pre>ID: {$question->id}\nNombre: {$question->name}\nTipo: {$question->qtype}</pre>";
} else {
    echo "<p style='color: red;'>✗ Pregunta NO existe</p>";
    die();
}

// 2. Verificar prefijo de tablas
echo "<h3>2. Configuración de Base de Datos</h3>";
echo "<pre>";
echo "Prefijo de tablas: " . $CFG->prefix . "\n";
echo "Tipo de BD: " . $CFG->dbtype . "\n";
echo "Familia BD: " . $CFG->dblibrary . "\n";
echo "</pre>";

// 3. Buscar todas las referencias a esta pregunta
echo "<h3>3. Tablas que referencian esta pregunta</h3>";

$tables_to_check = array(
    'quiz_slots' => 'questionid',
    'question_answers' => 'question',
    'question_hints' => 'questionid',
    'question_multichoice' => 'questionid',
    'question_truefalse' => 'question',
    'question_shortanswer' => 'question',
    'question_calculated' => 'question',
    'question_match' => 'question',
    'question_match_sub' => 'questionid',
    'question_numerical' => 'question',
    'question_essay' => 'question',
    'question_gapselect' => 'questionid',
    'question_ddwtos' => 'questionid',
    'question_ddmarker' => 'questionid',
    'question_ddimageortext' => 'questionid',
    'qtype_essay_options' => 'questionid',
    'qtype_match_options' => 'questionid',
    'qtype_match_subquestions' => 'questionid',
);

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Tabla</th><th>Campo</th><th>Registros</th><th>Existe tabla</th></tr>";

foreach ($tables_to_check as $table => $field) {
    echo "<tr>";
    echo "<td>{$CFG->prefix}{$table}</td>";
    echo "<td>{$field}</td>";

    // Verificar si la tabla existe
    try {
        $count = $DB->count_records($table, array($field => $questionid));
        echo "<td style='color: " . ($count > 0 ? 'orange' : 'green') . ";'>{$count}</td>";
        echo "<td style='color: green;'>✓ Sí</td>";
    } catch (Exception $e) {
        echo "<td>N/A</td>";
        echo "<td style='color: gray;'>✗ No existe</td>";
    }

    echo "</tr>";
}

echo "</table>";

// 4. Verificar constraints de foreign keys
echo "<h3>4. Intentando obtener información de Foreign Keys</h3>";
try {
    $sql = "SELECT
        TABLE_NAME,
        COLUMN_NAME,
        CONSTRAINT_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = '{$CFG->prefix}question'
    AND TABLE_SCHEMA = DATABASE()";

    $fks = $DB->get_records_sql($sql);

    if ($fks) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Tabla</th><th>Campo</th><th>Constraint</th></tr>";
        foreach ($fks as $fk) {
            echo "<tr>";
            echo "<td>{$fk->table_name}</td>";
            echo "<td>{$fk->column_name}</td>";
            echo "<td>{$fk->constraint_name}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron foreign keys o no tienes permisos para verlas.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>No se pudo consultar foreign keys: " . $e->getMessage() . "</p>";
}

// 5. Intentar eliminar paso a paso y reportar errores
echo "<h3>5. Simulación de Eliminación (sin commit)</h3>";

$transaction = $DB->start_delegated_transaction();

try {
    echo "<ol>";

    // quiz_slots
    echo "<li>Eliminando de quiz_slots... ";
    try {
        $deleted = $DB->delete_records('quiz_slots', array('questionid' => $questionid));
        echo "<span style='color: green;'>✓ OK ($deleted registros)</span></li>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ ERROR: " . $e->getMessage() . "</span></li>";
    }

    // question_answers
    echo "<li>Eliminando de question_answers... ";
    try {
        $deleted = $DB->delete_records('question_answers', array('question' => $questionid));
        echo "<span style='color: green;'>✓ OK ($deleted registros)</span></li>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ ERROR: " . $e->getMessage() . "</span></li>";
    }

    // question_hints
    echo "<li>Eliminando de question_hints... ";
    try {
        $deleted = $DB->delete_records('question_hints', array('questionid' => $questionid));
        echo "<span style='color: green;'>✓ OK ($deleted registros)</span></li>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ ERROR: " . $e->getMessage() . "</span></li>";
    }

    // Tipos específicos
    $type_tables = array(
        'question_multichoice' => 'questionid',
        'question_truefalse' => 'question',
        'question_shortanswer' => 'question',
        'question_calculated' => 'question',
        'question_match' => 'question',
        'question_numerical' => 'question',
        'question_essay' => 'question',
    );

    foreach ($type_tables as $table => $field) {
        echo "<li>Eliminando de $table... ";
        try {
            $deleted = $DB->delete_records($table, array($field => $questionid));
            echo "<span style='color: green;'>✓ OK ($deleted registros)</span></li>";
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗ ERROR: " . $e->getMessage() . "</span></li>";
        }
    }

    // Finalmente la pregunta
    echo "<li>Eliminando pregunta principal... ";
    try {
        $deleted = $DB->delete_records('question', array('id' => $questionid));
        echo "<span style='color: green;'>✓ OK ($deleted registros)</span></li>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ ERROR: " . $e->getMessage() . "</span></li>";
    }

    echo "</ol>";

    echo "<p style='color: blue; font-weight: bold;'>⚠️ ROLLBACK - Nada se eliminó realmente (solo simulación)</p>";
    $transaction->rollback(new Exception("Rollback intencional - solo diagnóstico"));

} catch (Exception $e) {
    $transaction->rollback($e);
    echo "<p style='color: red;'>Error general: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<ol>";
echo "<li>Copia TODA esta información</li>";
echo "<li>Envíala al desarrollador</li>";
echo "<li>Especialmente importante: la sección de 'Tablas que referencian' y cualquier error en rojo</li>";
echo "</ol>";

echo "<p><a href='index.php'>← Volver a búsqueda</a></p>";

echo $OUTPUT->footer();
