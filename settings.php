<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('tools',
        new admin_externalpage(
            'tool_questionsearch',
            get_string('pluginname', 'tool_questionsearch'),
            new moodle_url('/admin/tool/questionsearch/index.php'),
            'tool/questionsearch:use'
        )
    );
}
