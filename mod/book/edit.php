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
 * Edit book chapter
 *
 * @package    mod_book
 * @copyright  2004-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/locallib.php');
require_once(__DIR__.'/edit_form.php');

$cmid       = required_param('cmid', PARAM_INT);  // Book Course Module ID
$chapterid  = optional_param('id', 0, PARAM_INT); // Chapter ID
$pagenum    = optional_param('pagenum', 0, PARAM_INT);
$subchapter = optional_param('subchapter', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('book', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/book:edit', $context);

$PAGE->set_url('/mod/book/edit.php', array('cmid'=>$cmid, 'id'=>$chapterid, 'pagenum'=>$pagenum, 'subchapter'=>$subchapter));
$PAGE->set_pagelayout('admin'); // TODO: Something. This is a bloody hack!

if ($chapterid) {
    $chapter = $DB->get_record('book_chapters', array('id'=>$chapterid, 'bookid'=>$book->id), '*', MUST_EXIST);
} else {
    $chapter = new stdClass();
    $chapter->id         = null;
    $chapter->subchapter = $subchapter;
    $chapter->pagenum    = $pagenum + 1;
}
$chapter->cmid = $cm->id;

$options = array('noclean'=>true, 'subdirs'=>true, 'maxfiles'=>-1, 'maxbytes'=>0, 'context'=>$context);
$chapter = file_prepare_standard_editor($chapter, 'content', $options, $context, 'mod_book', 'chapter', $chapter->id);
$content = '';

$mform = new book_chapter_edit_form(null, array('chapter'=>$chapter, 'options'=>$options, 'content'=>$content));

// If data submitted, then process and store.
if ($mform->is_cancelled()) {
    if (empty($chapter->id)) {
        redirect("view.php?id=$cm->id");
    } else {
        redirect("view.php?id=$cm->id&chapterid=$chapter->id");
    }

}
 else if ($data = $mform->get_data()) {

    if ($data->id) {
        // store the files
        $data->timemodified = time();
        $data = file_postupdate_standard_editor($data, 'content', $options, $context, 'mod_book', 'chapter', $data->id);
        $DB->update_record('book_chapters', $data);
        $DB->set_field('book', 'revision', $book->revision+1, array('id'=>$book->id));
        $chapter = $DB->get_record('book_chapters', array('id' => $data->id));

        \mod_book\event\chapter_updated::create_from_chapter($book, $context, $chapter)->trigger();

    } else {
        // adding new chapter
        $data->bookid        = $book->id;
        $data->hidden        = 0;
        $data->timecreated   = time();
        $data->timemodified  = time();
        $data->importsrc     = '';
        $data->content       = '';          // updated later
        $data->contentformat = FORMAT_HTML; // updated later

        // make room for new page
        $sql = "UPDATE {book_chapters}
                   SET pagenum = pagenum + 1
                 WHERE bookid = ? AND pagenum >= ?";
        $DB->execute($sql, array($book->id, $data->pagenum));

        $data->id = $DB->insert_record('book_chapters', $data);

        // store the files
        $data = file_postupdate_standard_editor($data, 'content', $options, $context, 'mod_book', 'chapter', $data->id);
        $DB->update_record('book_chapters', $data);
        $DB->set_field('book', 'revision', $book->revision+1, array('id'=>$book->id));
        $chapter = $DB->get_record('book_chapters', array('id' => $data->id));

        \mod_book\event\chapter_created::create_from_chapter($book, $context, $chapter)->trigger();
    }

    book_preload_chapters($book); // fix structure
    redirect("view.php?id=$cm->id&chapterid=$data->id");
}
else if ($mform->no_submit_button_pressed()) {    
    //$amount = count($mform->get_submit_value('tabtitle'));
    if ($mform->get_submit_value("addotags")) {
        $in = '';
        $tabs = '';
        for ($x = 0; $x < count($mform->get_submit_value('tabtitle')); $x++) {
            $y = $x + 1;
            // $in .= '<div class="tab'.$y.'">'.$mform->get_submit_value('tabtitle['.$x.']').'</div>
            //';
            $in .= "\n\t<button class='tablinks' onclick=\"openCity(event, 'Tab".$x."')\">".$mform->get_submit_value('tabtitle['.$x.']')."</button>";

            $arr = array_values($mform->get_submit_value('tabcont['.$x.']'));

            $tabs .= "<div id='Tab".$x."' class='tabcontent'>"."\n\t"."<h3>".$mform->get_submit_value('tabtitlecont['.$x.']')."</h3>"."\n\t".$arr[0]."\n"."</div>\n";
        }

        $content = '<div class="tab">'.$in."\n".'</div>' . "\n" . $tabs;

        $mform = new book_chapter_edit_form(null, array('chapter'=>$chapter, 'options'=>$options, 'content'=>$content));
    }
    else{
        //sanfona 
        for ($x = 0; $x < count($mform->get_submit_value('sanfonatitle')); $x++) {
            $y = $x + 1;
            // $in .= '<div class="tab'.$y.'">'.$mform->get_submit_value('tabtitle['.$x.']').'</div>
            //';

            $arr = array_values($mform->get_submit_value('sanfonacont['.$x.']'));

            $in2 .= "\n\t<button class=\"sanfona\">".$mform->get_submit_value('sanfonatitle['.$x.']')."</button>\n\t<div class=\"painel\">
                        " .$arr[0]."\n\t</div>\n";
        }

        $content2 = '<div class="sanfona_div">'.$in2."\n".'</div>' . "\n";



        $mform = new book_chapter_edit_form(null, array('chapter'=>$chapter, 'options'=>$options, 'content2'=>$content2));        
    }


}


//echo "<br><br><br><br><br><br><br><br>";
//var_dump($mform->content);


// Otherwise fill and print the form.
$PAGE->set_title($book->name);
$PAGE->set_heading($course->fullname);

if ($chapters = book_preload_chapters($book)) {
    book_add_fake_block($chapters, $chapter, $book, $cm);
}


echo $OUTPUT->header();
echo $OUTPUT->heading($book->name);


$mform->display();

echo $OUTPUT->footer();
