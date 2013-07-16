var nbcpq_visual_editor = false;

(function() {
    tinymce.create('tinymce.plugins.NBC_Pullquote_Shortcode', {
        init : function(ed, url) {
            ed.addButton('nbcpq', {
                title : window.nbcpqtext.button_title,
                cmd : 'nbcpq',
                image : url + '/icon.png'
            });

            ed.addCommand( 'nbcpq', function() {
                nbcpq_text = '';
                nbcpq_visual_editor = ed;
                nbcpq_visual_editor.focus();
                // check for selection...
                nbcpq_text = nbcpq_visual_editor.selection.getContent({format : 'text'});
                launch_pq_dialog(true);
            });
        },

        createControl : function(n, cm) {
            return null;
        },

        getInfo : function() {
            return {
                longname : window.nbcpqtext.button_title,
                author : 'WebDevStudios.com',
                authorurl : 'http://webdevstudios.com',
                infourl : 'http://webdevstudios.com',
                version : '1.0.0'
            };
        }
    });

    // Visual editor button
    tinymce.PluginManager.add( 'nbcpq', tinymce.plugins.NBC_Pullquote_Shortcode );

})();