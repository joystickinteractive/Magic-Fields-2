<?php 

class mf_custom_fields extends mf_admin {

  /**
   * this is the page where is displayed the list of fields of a certain custom field
   * @return none
   */
  function fields_list() {
    global $mf_domain;

    $pt = new mf_posttype();
    $post_type = $pt->get_post_type($_GET['post_type']);
    if(!$post_type){
      $post_type['core']['label'] = $_GET['post_type'];
      $post_type['core']['type'] = $_GET['post_type'];
    }

    print '<div class="wrap">';
    print '<h2>'.$post_type['core']['label'].'</h2>';
    print '<h3>'.__( 'Custom Fields', $mf_domain ).'<a href="admin.php?page=mf_dispatcher&mf_section=mf_custom_fields&mf_action=add_field&post_type='.$post_type['core']['type'].'" class="add-new-h2 button">'.__( 'Add new Custom Field', $mf_domain ).'</a>';
     print '<a href="admin.php?page=mf_dispatcher&mf_section=mf_custom_group&mf_action=add_group&post_type='.$post_type['core']['type'].'" class="add-new-h2 button">'.__( '+ Create a Group', $mf_domain ).'</a></h3>';
    print '</div>';

    //list cusmtom field of post type
    $groups = $this->get_groups_by_post_type($post_type['core']['type']);
    foreach( $groups as $group):
    $name = $group['label'];
    if($name != 'Magic Fields'){
      $name = sprintf('<a class="edit-group-h2" href="admin.php?page=mf_dispatcher&mf_section=mf_custom_group&mf_action=edit_group&custom_group_id=%s">%s</a>',$group['id'],$name);
      $name .= sprintf('<span class="mf_add_group_field">(<a href="#">create field</a>)</span>');
      $delete_link = 'admin.php?page=mf_dispatcher&init=true&mf_section=mf_custom_group&mf_action=delete_custom_group&custom_group_id='.$group['id'];
      $delete_link = wp_nonce_url($delete_link,'delete_custom_group');
      $name .= sprintf('<span class="mf_delete_group delete">(<a href="%s">delete group</a>)</span>',$delete_link);
    }
    ?>
      <h3><?php echo $name; ?></h3>
     <div>
     <table class="widefat fixed" cellspacing="0">
      <thead>
        <tr>
          <th scope="col" id="label" class="manage-column column-title" width="30%">label</th>
          <th scope="col" id="type" class="manage-column column-title" width="30%">type</th>
          <th scope="col" id="decription" class="manage-column column-title" width="30%">description</th>
        </tr>
      </thead>
      <tfoot>
         <tr>
          <th scope="col" id="label" class="manage-column column-title" width="30%">label</th>
          <th scope="col" id="type" class="manage-column column-title" width="30%">type</th>
          <th scope="col" id="decription" class="manage-column column-title" width="30%">description</th>
        </tr>
      </tfood>
     </table>
     </div>
      <br />
   <?php
      endforeach;
  }

  /** 
   *  Page for add a new custom field
   */
  function add_field() {
    global $mf_domain;

    $data = $this->fields_form();
    $this->form_custom_field($data);
    ?>  
    <?php
  }
  
  function save_custom_field(){

    //save custom field
    $mf = $_POST['mf_field'];
    if($mf['core']['id']){
      //update
    
    }else{
      //insert
      $this->new_custom_field($mf);
    }
    $this->mf_redirect(null,null,array('message' => 'success'));
  }

  public function new_custom_field($data){
    global $wpdb;

    if( !isset($data['option']) ) $data['option'] = array();
   
    $sql = sprintf(
      "INSERT INTO %s ".
      "(name,label,description,post_type,type,requiered_field,duplicated,options) ".
      "VALUES ('%s','%s','%s','%s','%s',%s,%s,'%s')",
      MF_TABLE_CUSTOM_FIELDS,
      $data['core']['name'],
      $data['core']['label'],
      $data['core']['description'],
      $data['core']['post_type'],
      $data['core']['type'],
      $data['core']['required_field'],
      $data['core']['duplicate'],
      json_encode($data['option'])
    );
    
    $wpdb->query($sql);
  }

  public function get_custom_fields_post_type($post_type){
    GLOBAL $wpdb;
    $query = sprintf("SELECT * FROM %s WHERE post_type = '%s'", MF_TABLE_CUSTOM_FIELDS,$post_type);
    $fields = $wpdb->get_results($query, ARRAY_A);
    return $fields;
    
  }

  /**
   * Get the list of custom fields
   *
   * @return array
   */
  function get_custom_fields_name () {
    $path = MF_PATH.'/field_types/*';
    $folders = glob($path,GLOB_ONLYDIR); 
    
    $fields = array();

    foreach($folders as $folder) {
      $name = preg_match('/\/([\w\_]+)\_field$/i',$folder,$name_match);
      $fields[$name_match[1]] = $name_match[1];
    }

    return $fields;
  }


  public function fields_form() {
    global $mf_domain;

    $custom_fields = $this->get_custom_fields_name();
    $post_type = ($_GET['post_type'])? $_GET['post_type'] : '';
    $custom_field_id = isset($_GET['custom_field_id'])? $_GET['custom_field_id']: '';
    $data = array(
      'core'  => array(
        'post_type' => array(
          'type' => 'hidden',
          'id'   => 'customfield-post_type',
          'name' => 'mf_field[core][post_type]',
          'value' => $post_type
        ),
        'id' => array(
          'type' => 'hidden',
          'id'   => 'customfield_id',
          'name'  => 'mf_field[core][id]',
          'value' => $custom_field_id
        ),
        'type'  => array(
          'type'        =>  'select',
          'id'          =>  'customfield-type',
          'label'       =>  __('Type of Custom Field',$mf_domain),
          'name'        =>  'mf_field[core][type]',
          'default'     =>  '',
          'options'      =>  $custom_fields,
          'description' =>  __( 'Select the type of custom field', $mf_domain ),
          'value'       =>  '',
          'add_empty'   =>  true
        ),
        'label'  => array(
          'type'        =>  'text',
          'id'          =>  'customfield-label',
          'label'       =>  __('Label',$mf_domain),
          'name'        =>  'mf_field[core][label]',
          'description' =>  __( 'The label of the field', $mf_domain),
          'div_class'   =>  'form-required',
          'value'       =>  ''

        ),
        'name'  => array(
          'type'        =>  'text',
          'id'          =>  'customfield-name',
          'label'       =>  __('Name',$mf_domain),
          'name'        =>  'mf_field[core][name]',
          'description' =>  __( 'The name only accept letters and numbers (lowercar)', $mf_domain),
          'div_class'   =>  'form-required',
          'value'       =>  ''
        ),
        'description' =>  array(
          'type'        =>  'text',
          'label'       =>  __('Description',$mf_domain), 
          'name'        =>  'mf_field[core][description]',
          'description' =>  __( 'Tell to the user about what is the field', $mf_domain ),
          'value'       =>  '',
          'id'          => 'customfield-description'
        ),
        'required_field'    =>  array(
          'type'        =>  'checkbox',
          'label'       =>  __('required',$mf_domain),
          'name'        =>  'mf_field[core][required_field]',
          'description' =>  __( 'this field is required', $mf_domain ),
          'id'          =>  'customfield-required',
          'value'       =>  0
        ),
        'duplicated'  =>  array(
          'type'        =>  'checkbox',
          'label'       =>  __('Can be duplicated',$mf_domain),
          'name'        =>  'mf_field[core][duplicate]',
          'description' =>  __('this field is duplicable?',$mf_domain),
          'value'       =>  0,
          'id'          =>  'customfield-duplicated',

        )
      )
    );

    return $data;
  }

  function form_custom_field( $data ) {
    global $mf_domain;
    ?>
    <div class="wrap">
      <h2><?php _e('Create Custom Field', $mf_domain);?></h2>


     <form id="addCustomField" method="post" action="admin.php?page=mf_dispatcher&init=true&mf_section=mf_custom_fields&mf_action=save_custom_field" class="validate">
      <div class="alignleft fixed" id="mf_add_custom_field">
        <?php foreach( $data['core'] as $core ):?>
          <?php if( $core['type'] == 'hidden' ): ?>
	          <?php $this->mf_form_hidden($core); ?>
          <?php elseif( $core['type'] == 'text' ):?>
	          <div class="form-field mf_form <?php echo $core['div_class']; ?>">
              <?php $this->mf_form_text($core); ?>
            </div>
          <?php elseif( $core['type'] == "select" ):?>
            <div class="form-field mf_form <?php echo $core['div_class']; ?>">
              <?php $this->mf_form_select($core); ?>
            </div>
          <?php elseif( $core['type'] == "checkbox" ):?>
            <fieldset>
              <div class="form-field mf_form <?php echo $core['div_class']; ?>">
              <?php $this->mf_form_checkbox($core);?>
              </div>
            </fieldset>
          <?php endif;?> 
        <?php endforeach;?>
      	<p class="submit">
      	  <a style="color:black" href="admin.php?page=mf_dispatcher" class="button">Cancel</a>
      	  <input type="submit" class="button" name="submit" id="submit" value="Save Custom Field">
      	</p>
      </div>
      <div class="widefat mf_form_right">
        <h4>Options of field</h4>
        <div  id="options_field">
          <p>By default on this box will be displayed a information about custom fields, after the  custom field be selected, this box will be displayed some extra options of the field (if required) or a information about the selected field</p>
        </div>
      </div>
    </div>
</form>
    <script type="text/javascript">
      jQuery(document).ready(function($) {
        $('#customfield-type').change( function(){
          type = $(this).val();
          if(type != ''){
            jQuery.post(
               ajaxurl,
               {
                  'action':'load_field_type',
                  'field_type': type
               },
               function(response){
                 $("#options_field").empty().append(response);
               }
            );
          }
        });
      });
    </script>
  <?php
  }
}
