<!-- TinyMCE
Carregue l'editor a la página y tal. Abaix están les plantilles.
 -->
	<script type="text/javascript">
		tinyMCE.init({
			// General opts
			mode : "specific_textareas", editor_selector : "miniEditor", theme : "advanced", language : "en",
			plugins : "",
			width : "620", height: "200",

			document_base_url : "<?php echo rurl();?>",
			relative_urls : false,
			remove_script_host : false,
				
			// Ocions del tema
			//theme_advanced_buttons1 : "cleanup,code,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,|,forecolor,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,|,template,image,media",
			//theme_advanced_buttons2 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,styleprops,attribs,|,visualchars,nonbreaking,charmap",
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "",
			theme_advanced_resizing : false,
			
			// Content CSS (copia de les parts del css que s'empleen al article)
			content_css : "<?php echo rurl();?>/css/mceEditor.css",
	
			// Valors de sustitució de plantilles del tema
			template_replace_values : {
				username : "000000",
				staffid : "000000"
			}
		});
		
		tinyMCE.init({
			mode : "specific_textareas", editor_selector : "commentEditor", theme : "advanced", language : "en",
			plugins : "",
			width : "620", height: "100",
			document_base_url : "<?php echo rurl();?>",
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough",
			theme_advanced_toolbar_location : "bottom",
			theme_advanced_toolbar_align : "center",
			theme_advanced_statusbar_location : "",
			theme_advanced_resizing : false,
			content_css : "<?php echo rurl();?>/css/mceEditor.css"
		});
		
	</script>
<!-- /TinyMCE -->
