jQuery(document).ready(function($) {
         $('.txtrhinodepts').select2({
		allowClear: true
		
	});
	
        $(".txtrhinodepts").select().change(function(){ 
		
		$str_selected = jQuery(this).select2().val();
		
		if($str_selected != null){
			$pos = $str_selected.lastIndexOf("select_all");
			if($pos >= 0){
				jQuery(this).find('option').each(function() {
					if(jQuery(this).val() == "select_all"){
						jQuery(this).attr("selected",false);
						
					}else{
						jQuery(this).attr("selected","selected");
					}
					jQuery(this).trigger("change");
				});

			}
		}
		
	});
	});