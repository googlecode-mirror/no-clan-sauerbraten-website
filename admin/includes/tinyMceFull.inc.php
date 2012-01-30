<script type="text/javascript">
    tinyMCE.init({
        // FULL EDITOR / General opts
        mode : "specific_textareas", editor_selector : "fullEditor", theme : "advanced", language : "en",
        //plugins : "style,table,advhr,advimage,advlink,media,contextmenu,paste,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist",
        plugins : "style,table,advhr,advimage,advlink,media,contextmenu,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist",
        width : "620", height: "600",

        document_base_url : "<?php echo rurl();?>/admin/",
        relative_urls : false,
        remove_script_host : false,
            
        // Theme options
        //theme_advanced_buttons1 : "cleanup,code,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,|,forecolor,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,|,template,image,media",
        //theme_advanced_buttons2 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,styleprops,attribs,|,visualchars,nonbreaking,charmap",
        theme_advanced_buttons1 : "cleanup,code,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,|,image,media",
        theme_advanced_buttons2 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,styleprops,attribs,|,visualchars,nonbreaking,charmap",
        theme_advanced_buttons3 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : false,
        
        // Content CSS (copia de les parts del css que s'empleen al article)
        content_css : "<?php echo rurl();?>/admin/includes/mceFull.css",

        // Valors de sustitució de plantilles del tema
        template_replace_values : {
            username : "000000",
            staffid : "000000"
        },

        // Templates
        template_templates :
        [
            {
                /*
                title : "title",
                src : "<?php echo rurl();?>/admin/tinymce/template_name.htm",
                description : "Description of the template."
                */
            }
        ]
    });
    
    tinyMCE.init({
			// MINI EDITOR / General opts
			mode : "specific_textareas", editor_selector : "miniEditor", theme : "advanced", language : "en",
			plugins : "",
			width : "620", height: "200",

			document_base_url : "<?php echo rurl();?>",
			relative_urls : false,
			remove_script_host : false,

			theme_advanced_buttons1 : "bold,italic,underline,strikethrough",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "",
			theme_advanced_resizing : false,
			
			// Content CSS
			content_css : "<?php echo rurl();?>/css/mceEditor.css",
	
			// Valors de sustitució de plantilles del tema
			template_replace_values : {
				username : "000000",
				staffid : "000000"
			},

			// Template list (Rel. css/mceEditor.css)
			template_templates :
            [
                /*
                {
                    title : "title",
                    src : "src.htm",
                    description : "description"
                }
                */
			]
		});
</script>
