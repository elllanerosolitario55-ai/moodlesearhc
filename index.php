<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('tool_questionsearch');

require_capability('tool/questionsearch:use', context_system::instance());

$PAGE->set_url(new moodle_url('/admin/tool/questionsearch/index.php'));
$PAGE->set_title(get_string('pluginname', 'tool_questionsearch'));
$PAGE->set_heading(get_string('pluginname', 'tool_questionsearch'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('searchpage', 'tool_questionsearch'));

// Get all courses for dropdown
$courses = $DB->get_records_menu('course', array(), 'fullname', 'id,fullname');

?>

<style>
.questionsearch-form {
    max-width: 800px;
    margin: 20px auto;
    padding: 30px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
}
.form-group input[type="text"],
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}
.form-group small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}
.checkbox-group {
    display: flex;
    gap: 20px;
    margin-top: 10px;
}
.checkbox-group label {
    display: flex;
    align-items: center;
    font-weight: normal;
}
.checkbox-group input {
    margin-right: 8px;
}
.search-button {
    background: #0f6cbf;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}
.search-button:hover {
    background: #0a5085;
}
.search-areas {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
</style>

<form action="search.php" method="get" class="questionsearch-form">
    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">

    <div class="form-group">
        <label for="searchterm"><?php echo get_string('searchterm', 'tool_questionsearch'); ?> *</label>
        <input type="text" id="searchterm" name="searchterm" required>
        <small><?php echo get_string('searchtermdesc', 'tool_questionsearch'); ?></small>
    </div>

    <div class="form-group">
        <label for="replaceterm"><?php echo get_string('replaceterm', 'tool_questionsearch'); ?></label>
        <input type="text" id="replaceterm" name="replaceterm">
        <small><?php echo get_string('replacetermdesc', 'tool_questionsearch'); ?></small>
    </div>

    <div class="form-group">
        <label for="courseid"><?php echo get_string('selectcourse', 'tool_questionsearch'); ?></label>
        <select id="courseid" name="courseid">
            <option value="0"><?php echo get_string('allcourses', 'tool_questionsearch'); ?></option>
            <?php foreach ($courses as $id => $name): ?>
                <?php if ($id > 0): // Skip site course ?>
                    <option value="<?php echo $id; ?>"><?php echo s($name); ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label><?php echo get_string('searchin', 'tool_questionsearch'); ?></label>
        <div class="search-areas">
            <label>
                <input type="checkbox" name="search_questiontext" value="1" checked>
                <?php echo get_string('questiontext', 'tool_questionsearch'); ?>
            </label>
            <label>
                <input type="checkbox" name="search_feedback" value="1" checked>
                <?php echo get_string('generalfeedback', 'tool_questionsearch'); ?>
            </label>
            <label>
                <input type="checkbox" name="search_answers" value="1" checked>
                <?php echo get_string('answers', 'tool_questionsearch'); ?>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="checkbox-group">
            <label>
                <input type="checkbox" name="casesensitive" value="1">
                <?php echo get_string('casesensitive', 'tool_questionsearch'); ?>
            </label>
            <label>
                <input type="checkbox" name="exactmatch" value="1">
                <?php echo get_string('exactmatch', 'tool_questionsearch'); ?>
            </label>
        </div>
    </div>

    <div class="form-group">
        <button type="submit" class="search-button">
            <?php echo get_string('searchbutton', 'tool_questionsearch'); ?>
        </button>
    </div>
</form>

<?php
echo $OUTPUT->footer();
