<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/question/engine/lib.php');

admin_externalpage_setup('tool_questionsearch');

require_capability('tool/questionsearch:use', context_system::instance());
require_sesskey();

$PAGE->set_url(new moodle_url('/admin/tool/questionsearch/replace.php'));
$PAGE->set_title(get_string('replace', 'tool_questionsearch'));
$PAGE->set_heading(get_string('replace', 'tool_questionsearch'));

// Get parameters
$searchterm = required_param('searchterm', PARAM_RAW);
$replaceterm = required_param('replaceterm', PARAM_RAW);
$casesensitive = optional_param('casesensitive', 0, PARAM_INT);
$selected = optional_param_array('selected', array(), PARAM_RAW);

if (empty($selected)) {
    redirect(new moodle_url('/admin/tool/questionsearch/index.php'),
             get_string('noresults', 'tool_questionsearch'),
             null,
             \core\output\notification::NOTIFY_ERROR);
}

$replacements = 0;
$errors = array();

// Start transaction for safety
$transaction = $DB->start_delegated_transaction();

try {
    foreach ($selected as $item) {
        $data = json_decode($item, true);

        if (!$data) {
            continue;
        }

        // Perform replacement based on location
        switch ($data['field']) {
            case 'questiontext':
                $question = $DB->get_record('question', array('id' => $data['questionid']));
                if ($question) {
                    if ($casesensitive) {
                        $newtext = str_replace($searchterm, $replaceterm, $question->questiontext);
                    } else {
                        $newtext = str_ireplace($searchterm, $replaceterm, $question->questiontext);
                    }

                    if ($newtext !== $question->questiontext) {
                        $question->questiontext = $newtext;
                        $question->timemodified = time();
                        $DB->update_record('question', $question);
                        $replacements++;

                        // Purge question cache
                        question_bank::notify_question_edited($question->id);
                    }
                }
                break;

            case 'generalfeedback':
                $question = $DB->get_record('question', array('id' => $data['questionid']));
                if ($question) {
                    if ($casesensitive) {
                        $newfeedback = str_replace($searchterm, $replaceterm, $question->generalfeedback);
                    } else {
                        $newfeedback = str_ireplace($searchterm, $replaceterm, $question->generalfeedback);
                    }

                    if ($newfeedback !== $question->generalfeedback) {
                        $question->generalfeedback = $newfeedback;
                        $question->timemodified = time();
                        $DB->update_record('question', $question);
                        $replacements++;

                        // Purge question cache
                        question_bank::notify_question_edited($question->id);
                    }
                }
                break;

            case 'answer':
                if (isset($data['answerid'])) {
                    $answer = $DB->get_record('question_answers', array('id' => $data['answerid']));
                    if ($answer) {
                        if ($casesensitive) {
                            $newanswer = str_replace($searchterm, $replaceterm, $answer->answer);
                        } else {
                            $newanswer = str_ireplace($searchterm, $replaceterm, $answer->answer);
                        }

                        if ($newanswer !== $answer->answer) {
                            $answer->answer = $newanswer;
                            $DB->update_record('question_answers', $answer);
                            $replacements++;

                            // Purge question cache
                            question_bank::notify_question_edited($data['questionid']);
                        }
                    }
                }
                break;
        }
    }

    // Commit transaction
    $transaction->allow_commit();

    // Purge all caches to ensure changes are reflected
    purge_all_caches();

    redirect(new moodle_url('/admin/tool/questionsearch/index.php'),
             get_string('replacesuccess', 'tool_questionsearch', $replacements),
             null,
             \core\output\notification::NOTIFY_SUCCESS);

} catch (Exception $e) {
    $transaction->rollback($e);

    redirect(new moodle_url('/admin/tool/questionsearch/index.php'),
             get_string('replaceerror', 'tool_questionsearch') . ': ' . $e->getMessage(),
             null,
             \core\output\notification::NOTIFY_ERROR);
}
