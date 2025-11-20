<?php
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

foreach ($question_ids as $questionid) {
    try {
        if (!$DB->record_exists('question', array('id' => $questionid))) {
            $errors[] = "ID $questionid: No existe";
            continue;
        }

        $DB->delete_records('quiz_slots', array('questionid' => $questionid));
        $DB->delete_records('question_answers', array('question' => $questionid));
        $DB->delete_records('question_hints', array('questionid' => $questionid));
        $DB->delete_records('question_multichoice', array('questionid' => $questionid));
        $DB->delete_records('question_truefalse', array('question' => $questionid));
        $DB->delete_records('question_shortanswer', array('question' => $questionid));
        $DB->delete_records('question_calculated', array('question' => $questionid));
        $DB->delete_records('question_match', array('question' => $questionid));
        $DB->delete_records('question_match_sub', array('questionid' => $questionid));
        $DB->delete_records('question_numerical', array('question' => $questionid));
        $DB->delete_records('question_essay', array('question' => $questionid));

        if ($DB->delete_records('question', array('id' => $questionid))) {
            $deleted++;
        } else {
            $errors[] = "ID $questionid: No se pudo eliminar";
        }

    } catch (Exception $e) {
        $errors[] = "ID $questionid: " . $e->getMessage();
    }
}

purge_all_caches();

if ($deleted > 0) {
    $message = "$deleted pregunta(s) eliminada(s)";
    $type = \core\output\notification::NOTIFY_SUCCESS;
} else {
    $message = "No se eliminaron preguntas";
    $type = \core\output\notification::NOTIFY_ERROR;
}

if (!empty($errors)) {
    $message .= "\n\nDetalles:\n" . implode("\n", $errors);
}

redirect(new moodle_url('/admin/tool/questionsearch/index.php'), $message, null, $type);
