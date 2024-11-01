(function($) {
    $(function() {
        $('.js-select2 select').select2();

        $('.js-toggle-password-visibility').click(function(e) {
            e.preventDefault();
            var $this = $(this);
            var $input = $this.prev('input');
            var isPassword = $input.attr('type') == 'password';

            if (isPassword) {
                $input.attr('type', 'text');
                $this.text('Hide');
            } 
            else {
                $input.attr('type', 'password');
                $this.text('Show');                
            }
        });

        $('.js-select2-img select').select2({
            templateResult: formatState,
            templateSelection: formatState,
            minimumResultsForSearch: Infinity
        });
        
        function formatState(opt) {
            if (! opt.id) {
                return opt.text.toUpperCase();
            } 
        
            var optimage = $(opt.element).attr('data-img'); 

            if(! optimage) {
               return opt.text.toUpperCase();
            } 
            else {                    
                var $opt = $(
                //    '<span><img src="' + optimage + '" width="200px" /> ' + opt.text.toUpperCase() + '</span>'
                   '<span><img src="' + optimage + '" width="240px" /></span>'
                );
                return $opt;
            }
        };
    });
})(jQuery);