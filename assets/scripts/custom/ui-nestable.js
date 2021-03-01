var UINestable = function () {

    var updateOutput = function (e) {
	
        var list = e.length ? e : $(e.target),
            output = list.data('output');
        if (window.JSON) {
		    output.val(window.JSON.stringify(list.nestable('serialize'))); //, null, 2));
		} else {
            output.val('JSON browser support required for this demo.');
        }
    };


    return {
        //main function to initiate the module
        init: function () {

            
            // activate Nestable for list 2
            $('#nestable_list_2').nestable({
                group: 0,
				maxDepth: 1
            })
            .on('change', updateOutput);
			
			// output initial serialised data
            updateOutput($('#nestable_list_2').data('output', $('#nestable_list_2_output')));

        }

    };

}();
$(window).mousemove(function (e) {
    if ($('.dd-dragel') && $('.dd-dragel').length > 0 && !$('html, body').is(':animated')) {
        var bottom = $(window).height() - 50,
            top = 50;

        if (e.clientY > bottom && ($(window).scrollTop() + $(window).height() < $(document).height() - 100)) {
            $('html, body').animate({
                scrollTop: $(window).scrollTop() + 300
            }, 600);
        }
        else if (e.clientY < top && $(window).scrollTop() > 0) {
            $('html, body').animate({
                scrollTop: $(window).scrollTop() - 300
            }, 600);
        } else {
            $('html, body').finish();
        }
    }
});