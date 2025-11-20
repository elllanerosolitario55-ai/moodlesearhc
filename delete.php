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
            $question = $DB->get_record('question', array('id' => $questionid), 'id, name');

            if (!$question) {
                $errors[] = "Pregunta ID $questionid no encontrada";
                continue;
            }

            // 1. Delete from quiz_slots (removes question from quizzes)
            $DB->delete_records('quiz_slots', array('questionid' => $questionid));

            // 2. Delete answers
            $DB->delete_records('question_answers', array('question' => $questionid));

            // 3. Delete hints
            $DB->delete_records('question_hints', array('questionid' => $questionid));

            // 4. Delete question type-specific data
            $DB->delete_records('question_multichoice', array('questionid' => $questionid));
            $DB->delete_records('question_truefalse', array('question' => $questionid));
            $DB->delete_records('question_shortanswer', array('question' => $questionid));
            $DB->delete_records('question_calculated', array('question' => $questionid));
            $DB->delete_records('question_match', array('question' => $questionid));
            $DB->delete_records('question_match_sub', array('questionid' => $questionid));
            $DB->delete_records('question_numerical', array('question' => $questionid));
            $DB->delete_records('question_essay', array('question' => $questionid));

            // 5. Delete the main question record
            $deleted_main = $DB->delete_records('question', array('id' => $questionid));

            if ($deleted_main) {
                $deleted++;
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

    if (!empty($errors)) {
        $message .= "\n\nErrores: " . implode(", ", $errors);
        if ($deleted == 0) {
            $notify_type = \core\output\notification::NOTIFY_ERROR;
        }
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
