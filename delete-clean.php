<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('tool_questionsearch');
require_capability('tool/questionsearch:use', context_system::instance());
require_sesskey();

$PAGE->set_url(new moodle_url('/admin/tool/questionsearch/delete.php'));

$searchterm = required_param('searchterm', PARAM_RAW);
$selected = optional_param_array('selected', array(), PARAM_RAW);

if (empty($selected)) {
    redirect(new moodle_url('/admin/tool/questionsearch/index.php'),
             'No se seleccionaron preguntas',
             null,
             \core\output\notification::NOTIFY_ERROR);
}

$deleted = 0;
$errors = array();
$question_ids = array();

foreach ($selected as $item) {
    $data = json_decode($item, true);
    if ($data && isset($data['questionid'])) {
        $question_ids[] = $data['questionid'];
    }
}

$transaction = $DB->start_delegated_transaction();

try {
    foreach ($question_ids as $qid) {
        try {
            // Eliminar referencias primero (sin verificar si existen las tablas)
            @$DB->delete_records('quiz_slots', array('questionid' => $qid));
            @$DB->delete_records('question_answers', array('question' => $qid));
            @$DB->delete_records('question_hints', array('questionid' => $qid));
            @$DB->delete_records('question_multichoice', array('questionid' => $qid));
            @$DB->delete_records('question_truefalse', array('question' => $qid));
            @$DB->delete_records('question_shortanswer', array('question' => $qid));
            @$DB->delete_records('question_match', array('question' => $qid));
            @$DB->delete_records('question_match_sub', array('questionid' => $qid));
            @$DB->delete_records('question_numerical', array('question' => $qid));
            @$DB->delete_records('question_essay', array('question' => $qid));

            // Eliminar pregunta
            if ($DB->delete_records('question', array('id' => $qid))) {
                $deleted++;
            }
        } catch (Exception $e) {
            $errors[] = "ID $qid: " . $e->getMessage();
        }
    }

    $transaction->allow_commit();
    purge_all_caches();

    if ($deleted > 0) {
        $msg = "$deleted pregunta(s) eliminada(s)";
        $type = \core\output\notification::NOTIFY_SUCCESS;
    } else {
        $msg = "No se eliminaron preguntas";
        $type = \core\output\notification::NOTIFY_WARNING;
    }

    if (!empty($errors)) {
        $msg .= "\n\nErrores: " . implode(", ", array_slice($errors, 0, 3));
    }

    redirect(new moodle_url('/admin/tool/questionsearch/index.php'), $msg, null, $type);

} catch (Exception $e) {
    $transaction->rollback($e);
    redirect(new moodle_url('/admin/tool/questionsearch/index.php'),
             'Error: ' . $e->getMessage(),
             null,
             \core\output\notification::NOTIFY_ERROR);
}
