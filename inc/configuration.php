<?php 
// Save/Update configuration value
global $wpdb;
if(sanitize_text_field(!empty($_POST['submit']))){
	  $configVal = sanitize_text_field($_POST['wos_auto_forced']);
	  $splitorderproCondition = sanitize_text_field($_POST['wos_splitorderpro']);
	  $optionVal = get_option( 'wos_auto_forced' );
	  $option_name = 'wos_auto_forced' ;
	  $option_name_split_order = 'wos_splitorderpro' ;
      $new_value = $configVal;
      update_option( $option_name, $new_value );
      update_option( $option_name_split_order, $splitorderproCondition );
     echo "<div class='form-save-msg'>Changes Saved!</div>";
}
  $optionVal = get_option( 'wos_auto_forced' );
  $splitorderpro = get_option( 'wos_splitorderpro' );
?>

<?php
if (sanitize_text_field(!empty($_GET['vari'])) && sanitize_text_field($_GET['vari']) == 'yes') {} else {
    ?>
    <h1>General Configuration</h1>
    <div class="row">
        <div class="form-group">
            <form action="" method="post">
                <div><label for="sort" class="col-sm-2 control-label"> Enable split order </label>
                    <select class="form-control" name="wos_auto_forced" id="sort">
                        <option value="no" <?php
                        if ($optionVal == 'no') {
                            echo 'selected';
                        }
                        ?>>No</option>
                        <option value="yes" <?php
                        if ($optionVal == 'yes') {
                            echo 'selected';
                        }
                        ?>>Yes</option>
                    </select> 
                </div> 
                <br>
                <div>

                    <label for="sort" class="col-sm-2 control-label"> Split Order Conditions </label>
                    <select class="form-control" name="wos_splitorderpro" id="sort">
                        <option value="default" <?php
                        if ($splitorderpro == 'default') {
                            echo 'selected';
                        }
                        ?>>Default</option>
                        
						

                    </select> 
                                        <br><br>
                    <input type="submit" name="submit" value="save config">
                </div>
            </form>
        </div>
    </div>
<?php } ?>
