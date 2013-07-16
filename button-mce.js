var wdspq_visual_editor = false;

(function() {
    tinymce.create('tinymce.plugins.WDS_Pullquote_Shortcode', {
        init : function(ed, url) {
            ed.addButton('wdspq', {
                title : window.wdspqtext.button_title,
                cmd : 'wdspq',
                image : url + '/icon.png'
            });

            ed.addCommand( 'wdspq', function() {
                wdspq_text = '';
                wdspq_visual_editor = ed;
                wdspq_visual_editor.focus();
                // check for selection...
                wdspq_text = wdspq_visual_editor.selection.getContent({format : 'text'});
                launch_pq_dialog(true);
            });
        },

        createControl : function(n, cm) {
            return null;
        },

        getInfo : function() {
            return {
                longname : window.wdspqtext.button_title,
                author : 'WebDevStudios.com',
                authorurl : 'http://webdevstudios.com',
                infourl : 'http://webdevstudios.com',
                version : '1.0.0'
            };
        }
    });

    // Visual editor button
    tinymce.PluginManager.add( 'wdspq', tinymce.plugins.WDS_Pullquote_Shortcode );

})();