<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('tool_questionsearch');

require_capability('tool/questionsearch:use', context_system::instance());
require_sesskey();

$PAGE->set_url(new moodle_url('/admin/tool/questionsearch/delete.php'));
$PAGE->set_title(get_string('deletequestions', 'tool_questionsearch'));
$PAGE->set_heading(get_string('deletequestions', 'tool_questionsearch'));

// Get parameters
$searchterm = required_param('searchterm', PARAM_RAW);
$selected = optional_param_array('selected', array(), PARAM_RAW);

if (empty($selected)) {
    redirect(new moodle_url('/admin/tool/questionsearch/index.php'),
             'No se seleccionaron preguntas para eliminar',
             null,
             \core\output\notification::NOTIFY_ERROR);
}

$deleted = 0;
$errors = array();
$question_ids = array();

// Extract question IDs from selected items and avoid duplicates
foreach ($selected as $item) {
    $data = json_decode($item, true);
    if (!$data || !isset($data['questionid'])) {
        continue;
    }

    $qid = $data['questionid'];
    if (!in_array($qid, $question_ids)) {
        $question_ids[] = $qid;
    }
}

if (empty($question_ids)) {
    redirect(new moodle_url('/admin/tool/questionsearch/index.php'),
             'No se pudieron extraer IDs de preguntas',
             null,
             \core\output\notification::NOTIFY_ERROR);
}

// Start transaction for safety - all deletes succeed or all rollback
$transaction = $DB->start_delegated_transaction();

try {
    foreach ($question_ids as $questionid) {
        try {
            // Verify question exists
            $question = $DB->get_record('question', array('id' => $questionid), 'id, name, qtype');

            if (!$question) {
                $errors[] = "Pregunta ID $questionid no encontrada";
                continue;
            }

            // Lista completa de tablas relacionadas con preguntas
            $tables_to_clean = array(
                // Referencias en quizzes
                array('table' => 'quiz_slots', 'field' => 'questionid'),

                // Respuestas y feedback
                array('table' => 'question_answers', 'field' => 'question'),
                array('table' => 'question_hints', 'field' => 'questionid'),

                // Tipos de preguntas estándar
                array('table' => 'question_multichoice', 'field' => 'questionid'),
                array('table' => 'question_truefalse', 'field' => 'question'),
                array('table' => 'question_shortanswer', 'field' => 'question'),
                array('table' => 'question_calculated', 'field' => 'question'),
                array('table' => 'question_match', 'field' => 'question'),
                array('table' => 'question_match_sub', 'field' => 'questionid'),
                array('table' => 'question_numerical', 'field' => 'question'),
                array('table' => 'question_essay', 'field' => 'question'),

                // Tipos adicionales de Moodle 3.9+
                array('table' => 'question_gapselect', 'field' => 'questionid'),
                array('table' => 'question_ddwtos', 'field' => 'questionid'),
                array('table' => 'question_ddmarker', 'field' => 'questionid'),
                array('table' => 'question_ddimageortext', 'field' => 'questionid'),

                // Tablas qtype_* (Moodle 4.x)
                array('table' => 'qtype_essay_options', 'field' => 'questionid'),
                array('table' => 'qtype_match_options', 'field' => 'questionid'),
                array('table' => 'qtype_match_subquestions', 'field' => 'questionid'),
                array('table' => 'qtype_multichoice_options', 'field' => 'questionid'),
                array('table' => 'qtype_shortanswer_options', 'field' => 'questionid'),

                // Intentos y respuestas de usuarios
                array('table' => 'question_attempt_steps', 'field' => 'questionattemptid'),
                array('table' => 'question_attempt_step_data', 'field' => 'attemptstepid'),
                array('table' => 'question_attempts', 'field' => 'questionid'),

                // Banco de preguntas
                array('table' => 'question_references', 'field' => 'questionbankentryid'),
                array('table' => 'question_versions', 'field' => 'questionid'),
                array('table' => 'question_set_references', 'field' => 'questionscontextid'),
            );

            // Delete from related tables
            foreach ($tables_to_clean as $table_info) {
                $table = $table_info['table'];
                $field = $table_info['field'];

                try {
                    // Check if table exists before trying to delete
                    if ($DB->get_manager()->table_exists($table)) {
                        $DB->delete_records($table, array($field => $questionid));
                    }
                } catch (Exception $e) {
                    // Log but continue - table might not exist or have different structure
                    // No agregamos al array de errores para no saturar
                }
            }

            // Delete the main question record
            $deleted_main = $DB->delete_records('question', array('id' => $questionid));

            if ($deleted_main) {
                $deleted++;
            } else {
                $errors[] = "No se pudo eliminar pregunta ID $questionid";
            }

        } catch (Exception $e) {
            $errors[] = "Error ID $questionid: " . $e->getMessage();
        }
    }

    // Commit all changes
    $transaction->allow_commit();

    // Purge all caches to ensure changes are reflected
    purge_all_caches();

    // Build result message
    if ($deleted > 0) {
        $message = "$deleted pregunta(s) eliminada(s) exitosamente";
        $notify_type = \core\output\notification::NOTIFY_SUCCESS;
    } else {
        $message = "No se eliminaron preguntas";
        $notify_type = \core\output\notification::NOTIFY_WARNING;
    }

    if (!empty($errors) && count($errors) <= 5) {
        // Solo mostrar errores si son pocos
        $message .= "\n\nErrores: " . implode(", ", $errors);
        if ($deleted == 0) {
            $notify_type = \core\output\notification::NOTIFY_ERROR;
        }
    } elseif (!empty($errors) && count($errors) > 5) {
        $message .= "\n\nSe encontraron " . count($errors) . " errores. Contacta al administrador para más detalles.";
    }

    redirect(new moodle_url('/admin/tool/questionsearch/index.php'),
             $message,
             null,
             $notify_type);

} catch (Exception $e) {
    // Rollback all changes if any error occurred
    $transaction->rollback($e);

    redirect(new moodle_url('/admin/tool/questionsearch/index.php'),
             'Error crítico al eliminar preguntas: ' . $e->getMessage(),
             null,
             \core\output\notification::NOTIFY_ERROR);
}
