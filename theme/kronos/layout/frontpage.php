<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The frontpage layout.
 *
 * @package   theme_kronos
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$left = (!right_to_left());  // To know if to add 'pull-right' and 'desktop-first-column' classes in the layout for LTR.
echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Oswald:400,300,700' rel='stylesheet' type='text/css'>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes('two-column custom-theme'); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

    <header role="banner" class="navbar navbar-fixed-top moodle-has-zindex">
        <div id="brand" class="container">
            <div id="logo" >
                <a href="<?php echo $CFG->wwwroot;?>"><img src="<?php echo $OUTPUT->pix_url('logo', 'theme'); ?>" alt="0" /></a>
            </div>
            <?php echo $OUTPUT->login_info() ?>
        </div>
        <nav role="navigation" class="navbar-inner">
            <div class="container">
                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="nav-collapse collapse">
                    <?php echo $OUTPUT->custom_menu(); ?>
                </div>
            </div>
        </nav>
    </header>

    <section id="action-blocks">
        <div class="container">
            <div class="row-fluid">
                <?php echo $OUTPUT->blocks('action-one', 'span4'); ?>
                <?php echo $OUTPUT->blocks('action-two', 'span4'); ?>
                <?php echo $OUTPUT->blocks('action-three', 'span4'); ?>
            </div>
            <div class="row-fluid row2">
                <?php echo $OUTPUT->blocks('action-four', 'span12'); ?>
            </div>
            <div class="row-fluid row3">
                <?php echo $OUTPUT->blocks('action-five', 'span4'); ?>
                <?php echo $OUTPUT->blocks('action-six', 'span8'); ?>
            </div>
        </div>
    </section>

    <section id="page-wrap">
        <div id="page" class="container">

            <div id="page-content" class="row-fluid">
                <section id="region-main" class="span9<?php if ($left) { echo ' pull-right'; } ?>">
                    <?php
                    echo $OUTPUT->course_content_header();
                    echo $OUTPUT->main_content();
                    echo $OUTPUT->course_content_footer();
                    ?>
                </section>
                <?php
                $classextra = '';
                if ($left) {
                    $classextra = ' desktop-first-column';
                }
                echo $OUTPUT->blocks('side-pre', 'span3'.$classextra);
                ?>
            </div>

            <?php echo $OUTPUT->standard_end_of_body_html() ?>

        </div>
    </section>

    <footer id="page-footer">
        <div id="footer-content" class="container">
            <div class="row-fluid">
                <?php echo $OUTPUT->blocks('footer-one', 'span4'); ?>
                <?php echo $OUTPUT->blocks('footer-two', 'span4'); ?>
                <?php echo $OUTPUT->blocks('footer-three', 'span4'); ?>
            </div>
        </div>
    </footer>
    <div id="page-footer2">
        <span id="copyright">&copy; 2015, Kronos Incorporated. All rights reserved.</span>
        <?php echo $OUTPUT->login_info() ?>
    </div>

</body>
</html>