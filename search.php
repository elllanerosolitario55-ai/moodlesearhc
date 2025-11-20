<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('tool_questionsearch');

require_capability('tool/questionsearch:use', context_system::instance());
require_login();

$PAGE->set_url(new moodle_url('/admin/tool/questionsearch/search.php'));
$PAGE->set_title(get_string('results', 'tool_questionsearch'));
$PAGE->set_heading(get_string('results', 'tool_questionsearch'));

// Get search parameters
$searchterm = required_param('searchterm', PARAM_RAW);
$replaceterm = optional_param('replaceterm', '', PARAM_RAW);
$courseid = optional_param('courseid', 0, PARAM_INT);
$search_questiontext = optional_param('search_questiontext', 0, PARAM_INT);
$search_feedback = optional_param('search_feedback', 0, PARAM_INT);
$search_answers = optional_param('search_answers', 0, PARAM_INT);
$casesensitive = optional_param('casesensitive', 0, PARAM_INT);
$exactmatch = optional_param('exactmatch', 0, PARAM_INT);

if (empty($searchterm)) {
    redirect(new moodle_url('/admin/tool/questionsearch/index.php'),
             get_string('emptysearch', 'tool_questionsearch'),
             null,
             \core\output\notification::NOTIFY_ERROR);
}

// Prepare search pattern
$likepattern = "%{$searchterm}%";

// Results array
$results = array();

// Search in question text
if ($search_questiontext) {
    $sql = "SELECT id, name, questiontext, qtype FROM {question} WHERE questiontext LIKE ?";
    $questions = $DB->get_records_sql($sql, array($likepattern));

    foreach ($questions as $q) {
        $results[] = array(
            'questionid' => $q->id,
            'questionname' => $q->name,
            'questiontype' => $q->qtype,
            'courseid' => 0,
            'coursename' => 'Banco de preguntas',
            'quizid' => 0,
            'quizname' => 'No asignado',
            'location' => 'questiontext',
            'content' => $q->questiontext,
            'field' => 'questiontext'
        );
    }
}

// Search in general feedback
if ($search_feedback) {
    $sql = "SELECT id, name, generalfeedback, qtype FROM {question} WHERE generalfeedback LIKE ? AND generalfeedback != ''";
    $questions = $DB->get_records_sql($sql, array($likepattern));

    foreach ($questions as $q) {
        $results[] = array(
            'questionid' => $q->id,
            'questionname' => $q->name,
            'questiontype' => $q->qtype,
            'courseid' => 0,
            'coursename' => 'Banco de preguntas',
            'quizid' => 0,
            'quizname' => 'No asignado',
            'location' => 'generalfeedback',
            'content' => $q->generalfeedback,
            'field' => 'generalfeedback'
        );
    }
}

// Search in answers
if ($search_answers) {
    $sql = "SELECT qa.id, qa.answer, qa.question FROM {question_answers} qa WHERE qa.answer LIKE ?";
    $answers = $DB->get_records_sql($sql, array($likepattern));

    foreach ($answers as $a) {
        $question = $DB->get_record('question', array('id' => $a->question), 'name, qtype');
        if ($question) {
            $results[] = array(
                'questionid' => $a->question,
                'questionname' => $question->name,
                'questiontype' => $question->qtype,
                'courseid' => 0,
                'coursename' => 'Banco de preguntas',
                'quizid' => 0,
                'quizname' => 'No asignado',
                'location' => 'answer',
                'content' => $a->answer,
                'field' => 'answer',
                'answerid' => $a->id
            );
        }
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('results', 'tool_questionsearch'));

?>

<style>
.search-summary {
    background: #e3f2fd;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #0f6cbf;
}
.search-summary h3 {
    margin: 0 0 10px 0;
    color: #0f6cbf;
}
.search-summary p {
    margin: 5px 0;
}
.results-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.results-table th {
    background: #0f6cbf;
    color: white;
    padding: 12px;
    text-align: left;
    font-weight: bold;
}
.results-table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}
.results-table tr:hover {
    background: #f5f5f5;
}
.preview-text {
    max-width: 400px;
    overflow: hidden;
    text-overflow: ellipsis;
}
.highlight {
    background: yellow;
    font-weight: bold;
}
.action-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
}
.btn-primary {
    background: #0f6cbf;
    color: white;
}
.btn-secondary {
    background: #6c757d;
    color: white;
}
.btn-danger {
    background: #d32f2f;
    color: white;
}
.btn:hover {
    opacity: 0.85;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.btn:active {
    transform: translateY(0);
}
.back-link {
    display: inline-block;
    margin-top: 20px;
    color: #0f6cbf;
    text-decoration: none;
}
.back-link:hover {
    text-decoration: underline;
}
</style>

<div class="search-summary">
    <h3><?php echo get_string('found', 'tool_questionsearch', count($results)); ?></h3>
    <p><strong><?php echo get_string('searchterm', 'tool_questionsearch'); ?>:</strong> "<?php echo s($searchterm); ?>"</p>
    <?php if ($replaceterm): ?>
        <p><strong><?php echo get_string('replaceterm', 'tool_questionsearch'); ?>:</strong> "<?php echo s($replaceterm); ?>"</p>
    <?php endif; ?>
</div>

<?php if (empty($results)): ?>
    <div class="alert alert-info">
        <?php echo get_string('noresults', 'tool_questionsearch'); ?>
    </div>
<?php else: ?>
    <form action="replace.php" method="post" id="action-form">
        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
        <input type="hidden" name="searchterm" value="<?php echo s($searchterm); ?>">
        <input type="hidden" name="replaceterm" value="<?php echo s($replaceterm); ?>">
        <input type="hidden" name="casesensitive" value="<?php echo $casesensitive; ?>">
        <input type="hidden" name="action" id="form-action" value="replace">

        <div class="action-buttons">
            <button type="button" class="btn btn-secondary" onclick="selectAll()">
                Seleccionar Todo
            </button>
            <button type="button" class="btn btn-secondary" onclick="deselectAll()">
                Deseleccionar Todo
            </button>

            <?php if (!empty($replaceterm)): ?>
            <button type="submit" class="btn btn-primary" onclick="return confirmReplace()">
                ✏️ Reemplazar Seleccionadas
            </button>
            <?php endif; ?>

            <button type="button" class="btn btn-danger" onclick="return confirmAndDelete()" style="background: #d32f2f;">
                🗑️ Eliminar Seleccionadas
            </button>
        </div>

        <table class="results-table">
            <thead>
                <tr>
                    <?php if (true): ?>
                        <th width="40"><input type="checkbox" id="select-all"></th>
                    <?php endif; ?>
                    <th><?php echo get_string('course', 'tool_questionsearch'); ?></th>
                    <th><?php echo get_string('quiz', 'tool_questionsearch'); ?></th>
                    <th><?php echo get_string('question', 'tool_questionsearch'); ?></th>
                    <th><?php echo get_string('questiontype', 'tool_questionsearch'); ?></th>
                    <th><?php echo get_string('location', 'tool_questionsearch'); ?></th>
                    <th><?php echo get_string('preview', 'tool_questionsearch'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                <tr>
                    <?php if (true): ?>
                        <td>
                            <input type="checkbox" name="selected[]"
                                   value="<?php echo htmlspecialchars(json_encode($result)); ?>"
                                   class="result-checkbox">
                        </td>
                    <?php endif; ?>
                    <td><?php echo s($result['coursename']); ?></td>
                    <td><?php echo s($result['quizname']); ?></td>
                    <td><?php echo s($result['questionname']); ?></td>
                    <td><?php echo s($result['questiontype']); ?></td>
                    <td><?php echo s($result['location']); ?></td>
                    <td class="preview-text">
                        <?php
                        $content = strip_tags($result['content']);
                        echo htmlspecialchars(substr($content, 0, 200));
                        if (strlen($content) > 200) echo '...';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
<?php endif; ?>

<a href="index.php" class="back-link">← <?php echo get_string('searchpage', 'tool_questionsearch'); ?></a>

<script>
function selectAll() {
    document.querySelectorAll('.result-checkbox').forEach(function(cb) {
        cb.checked = true;
    });
}

function deselectAll() {
    document.querySelectorAll('.result-checkbox').forEach(function(cb) {
        cb.checked = false;
    });
}

var selectAllCheckbox = document.getElementById('select-all');
if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
        if (this.checked) {
            selectAll();
        } else {
            deselectAll();
        }
    });
}

function confirmReplace() {
    var checked = document.querySelectorAll('.result-checkbox:checked').length;
    if (checked === 0) {
        alert('Por favor seleccione al menos un elemento');
        return false;
    }
    document.getElementById('action-form').action = 'replace.php';
    return confirm('¿Está seguro que desea reemplazar ' + checked + ' elementos?');
}

function confirmAndDelete() {
    var checked = document.querySelectorAll('.result-checkbox:checked').length;
    if (checked === 0) {
        alert('Por favor seleccione al menos una pregunta');
        return false;
    }

    var confirmMsg = '⚠️ ADVERTENCIA CRÍTICA ⚠️\n\n' +
                     '¿Está COMPLETAMENTE SEGURO que desea ELIMINAR ' + checked + ' pregunta(s)?\n\n' +
                     '✗ Esta acción es IRREVERSIBLE\n' +
                     '✗ Se eliminarán del banco de preguntas\n' +
                     '✗ Se eliminarán de TODOS los cuestionarios\n' +
                     '✗ NO hay forma de recuperarlas\n\n' +
                     'Se recomienda hacer BACKUP antes de continuar.\n\n' +
                     '¿CONFIRMA que desea ELIMINAR estas preguntas?';

    if (confirm(confirmMsg)) {
        // Cambiar la acción del formulario a delete.php
        document.getElementById('action-form').action = 'delete.php';
        document.getElementById('action-form').submit();
        return true;
    }
    return false;
}
</script>

<?php
echo $OUTPUT->footer();
