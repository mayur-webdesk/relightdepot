var ContactsValidation = function () {
   var handleValidation2 = function() {
        // for more info visit the official plugin documentation: 
            // http://docs.jquery.com/Plugins/Validation

            var form2 = $('#contactsForm');
            var error2 = $('.alert-danger', form2);
            var success2 = $('.alert-success', form2);

            form2.validate({
                errorElement: 'div', //default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: true, // do not focus the last invalid input
                ignore: "",
                rules: {
                    firstname: {
                        minlength: 2,
                        required: true
                    },
					lastname: {
                        minlength: 2,
                        required: true
                    },
					email: {
                        minlength: 2,
                        required: true,
						email: true
                    },
					destination: {
                        required: true
                    },
					phone: {
                        number: true,
                        required: true
                    },
					comment: {
                        minlength: 2,
                        required: true
                    }
					
                },	
				
				
				invalidHandler: function (event, validator) { //display error alert on form submit              
                    success2.hide();
                    error2.show();
                    App.scrollTo(error2, -200);
                },

                errorPlacement: function (error, element) { // render error placement for each input type
                 
				  /* var icon = $(element).parent('.input-icon').children('i');
                    $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                    icon.removeClass("fa-warning").addClass("fa-check");*/
					  error.insertAfter(element);
				   
                },

                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group   
                },

                unhighlight: function (element) { // revert the change done by hightlight
                     $(element)
                        .closest('.form-group').removeClass('has-error');
                },

                success: function (label, element) {
				 label
                        .closest('.form-group').removeClass('has-error'); 
                   
                },

                submitHandler: function (form) {
				form.submit();
                    success2.show();
                    error2.hide();
                }
            });


    }

    return {
        //main function to initiate the module
        init: function () {

           handleValidation2();
          
        }

    };

}();
