<?php

/**
 * Display the calendar page.
 * @copyright 2003 Jon Papaioannou
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_calendar
 */

require_once('../config.php');
require_once($CFG->dirroot.'/course/lib.php');
// require_once($CFG->dirroot.'/calendar/lib.php');

$courseid = optional_param('course', SITEID, PARAM_INT);
$view = optional_param('view', 'upcoming', PARAM_ALPHA);


$url = new moodle_url('/sae/view.php');

// $url->param('time', $time);

$PAGE->set_url($url);

if ($courseid != SITEID && !empty($courseid)) {
    $course = $DB->get_record('course', array('id' => $courseid));
    $courses = array($course->id => $course);
    $issite = false;
    navigation_node::override_active_url(new moodle_url('/course/view.php', array('id' => $course->id)));
} else {
    $course = get_site();
    // $courses = calendar_get_default_courses();
    $issite = true;
}

require_course_login($course);

$pagetitle = '';

$strcalendar = get_string('sae', 'sae');


// Print title and header
$PAGE->set_pagelayout('standard');
$PAGE->set_title("$course->shortname: $strcalendar: $pagetitle");
$PAGE->set_heading($COURSE->fullname);

$renderer = $PAGE->get_renderer('core_calendar');
// $calendar->add_sidecalendar_blocks($renderer, true, $view);

echo $OUTPUT->header();
echo $renderer->start_layout();
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));
// echo $OUTPUT->heading(get_string('sae', 'sae'));

?>

      <div class="alert alert-success">
        <strong>Sucesso!</strong> Mensagem enviada para o sistema de atendimento.
      </div>

    <form class="well form-horizontal" action="ticket.php" method="post"  id="ticket_form" ">
      <fieldset>

      <!-- Form Name -->
      <h3 class=""><strong>Sistema de Atendimento ao aluno</strong></h3>
      <br>

       <?php  
        echo "<input  name='id' placeholder='Id' style='display: none;' class='form-control' value='".$USER->id ."'  type='text'>"
        ?>
       <?php 
        echo "<input  name='nome' placeholder='Nome' style='display: none;' class='form-control' value='" . $USER->username ."'  type='text'>"
        ?>   
          
        <?php   
        echo "<input name='email' placeholder='E-Mail Address' style='display: none;'  class='form-control' value='" . $USER->email . "' type='text'>"
        ?>
            <!-- Text input-->
             
      <div class="form-group">
        <label class=" control-label"><strong>Assunto</strong></label>  
          <div class=" inputGroupContainer">
          <div class="input-group">
              
          <select name="assunto1" class="form-control camada1" onchange="change_select(this);">
            <option selected="selected" value=""></option>
<!--             <option value="Reclamações sobre certificados">Reclamações sobre certificados</option>
            <option value="Reclamações sobre cursos">Reclamações sobre cursos</option>
            <option value="Reclamações sobre tutor">Reclamações sobre tutor</option>
            <option value="Reclamações sobre dados cadastrais">Reclamações sobre dados cadastrais</option>
            <option value="Dúvidas sobre certificados">Dúvidas sobre certificados</option>
            <option value="Dúvidas sobre cursos">Dúvidas sobre cursos</option>
            <option value="Dúvidas sobre dados cadastrais">Dúvidas sobre dados cadastrais</option> -->
            <option value="Dúvidas">Dúvidas</option>
            <option value="Reclamações">Reclamações</option>
            <option  value="Elogios">Elogios</option>
            <option value="Sugestões">Sugestões</option>
          </select>



          </div>
        </div>
      </div>

      <select name="assunto2"  class="form-control reclamacoes camada2" onchange="change_select_recl(this);" style="display: none; margin-bottom: 12px;">
        <option selected="selected" value=""></option>
        <option value="Reclamações sobre certificados">Reclamações sobre certificados</option>
        <option value="Reclamações sobre cursos">Reclamações sobre cursos</option>
        <option  value="Reclamações sobre tutor">Reclamações sobre tutor</option>
        <option value="Reclamações sobre dados cadastrais">Reclamações sobre dados cadastrais</option>
      </select>

      <select name="assunto3"  class="form-control reclamacoes-certificados camada3" style="display: none; margin-top: 12px; margin-bottom: 12px;">
        <option selected="selected" value=""></option>
        <option value="Meu certificado não foi gerado">Meu certificado não foi gerado</option>
        <option value="Certificado com dados incorretos">Certificado com dados incorretos</option>
      </select>

      <select name="assunto4"  class="form-control duvidas" style="display: none; margin-bottom: 12px;" onchange="change_select_duv(this);">
        <option selected="selected" value=""></option>
        <option value="Dúvidas sobre certificados">Dúvidas sobre certificados</option>
        <option value="Dúvidas sobre cursos">Dúvidas sobre cursos</option>
        <option value="Dúvidas sobre dados cadastrais">Dúvidas sobre dados cadastrais</option>
        <option value="Outras Dúvidas">Outras Dúvidas</option>
      </select>

      <p id="resposta1" style="margin-top: 12px; margin-bottom: 12px; display: none;">Bacon ipsum dolor amet alcatra fatback ball tip biltong corned beef porchetta pancetta jowl t-bone ribeye brisket. Landjaeger jowl tri-tip sausage cow flank. Biltong spare ribs short ribs jowl pork belly frankfurter drumstick tri-tip shankle doner ham hock. Kielbasa filet mignon ribeye boudin alcatra pork loin venison short loin picanha frankfurter short ribs tri-tip kevin. Landjaeger swine ground round, cupim drumstick fatback burgdoggen sirloin beef ribs picanha capicola frankfurter. Pig short loin tenderloin leberkas cow swine sirloin biltong chicken brisket shoulder.
      </p>
    
      <button type="button" class="btn header"><span>Escrever email</span></button>

          <div class="content" style=" display: none; margin-bottom: 12px;">
              
                <div class="form-group">
                  <label class=" control-label"><strong>Mensagem</strong></label>
                    <div class=" inputGroupContainer">
                    <div class="input-group">
                        
                          <textarea rows="10" class="form-control" name="mensagem" placeholder="Project Description"></textarea>
                  </div>
                  </div>
                </div>

                <!-- Button -->
                <div class="form-group">
                  <label class=" control-label"></label>
                  <div class="">
                    <button type="submit" class="btn btn-primary" >Enviar <span class="glyphicon glyphicon-send"></span></button>
                  </div>
                </div>
              
          </div>
      </div>



      </fieldset>
      </form>

      <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
      <script type="text/javascript">
          
          $(".header").click(function () {

              $header = $(this);
              //getting the next element
              $content = $header.next();
              //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
              $content.slideToggle(300, function () {
                  //execute this after slideToggle is done
                  //change text of header based on visibility of content div
                  $header.text(function () {
                      //change text based on condition
                      return $content.is(":visible") ? "Escrever email" : "Escrever email";
                  });
              });

          });
          
          // mensagem confirmando envio fica escondida ate que seja enviado o ticket
          $(".alert-success").hide();

          function change_select(sel) {
          
            if ($('.camada1').val() == "Reclamações") {
                $(".reclamacoes").css('display', 'block');
                
                $('select[name=assunto4]').val(null);
                
                if ($('.reclamacoes').val() == "Reclamações sobre certificados") {
                  $(".reclamacoes-certificados").css('display', 'block');
                  $('#resposta1').slideToggle(300);
                  //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
                }
            }else{
                $(".reclamacoes").css('display', 'none');
                $(".reclamacoes-certificados").css('display', 'none');
                
                $('select[name=assunto2]').val(null);
                $('select[name=assunto3]').val(null);
            }
            if ($('.camada1').val() == "Dúvidas") {
                $(".duvidas").css('display', 'block');

                $('select[name=assunto2]').val(null);
                $('select[name=assunto3]').val(null);
            }
            else{
              $(".duvidas").css('display', 'none');                
            }
            if ($('.camada1').val() == "Elogios" || $('.camada1').val() == "Sugestões"){
              $('select[name=assunto2]').val(null);
              $('select[name=assunto3]').val(null);
              $('select[name=assunto4]').val(null);
            } 
          }
          function change_select_recl(sel) {
            $('#resposta1').slideToggle(300);
            if ($('.reclamacoes').val() == "Reclamações sobre certificados") {
              $(".reclamacoes-certificados").css('display', 'block');
              $('select[name=assunto4]').val(null);
            }
            else{
              $(".reclamacoes-certificados").css('display', 'none');
              $('select[name=assunto3]').val(null);
            }
          }

          // $('select[name=assunto]').val(assunto);

         // this is the id of the form
          $("#ticket_form").submit(function(e) {

             if( document.getElementById('ticket_form').mensagem.value == "" )
             {
                alert( "Por favor preencha o campo mensagem!" );
                document.getElementById('ticket_form').mensagem.focus() ;
                return false;
             }

            var url = "ticket.php"; // the script where you handle the form input.
             
            $.ajax({
              type: "POST",
              url: url,
              data: $("#ticket_form").serialize(), // serializes the form's elements.
              success: function(data)
              {
              // alert("Ticket enviado com sucesso"); // show response from the php script.
                $(".alert-success").fadeTo(1000, 0.9);
              }
            });

            e.preventDefault(); // avoid to execute the actual submit of the form.
            setTimeout(function() {
              $('.alert-success').fadeOut('slow');
            }, 4000); // <-- time in milliseconds
          });
      </script>

<?php 



//Link to calendar export page.
// echo $OUTPUT->container_start('bottom');


// echo $OUTPUT->container_end();
echo html_writer::end_tag('div');
echo $renderer->complete_layout();
echo $OUTPUT->footer();


