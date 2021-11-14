<div class="ps-my-account" id= "resetForm">
    <div class="container">
        <div id= "phoneNotuseAlert" class="alert alert-warning alert-dismissible fade show" style= "display: none" role="alert">
          <strong>Phone not registered</strong> You typed phone not registered. Please try with registered one.
        </div>
        <div id= "phoneFormatAlert" class="alert alert-warning alert-dismissible fade show" style= "display: none" role="alert">
          <strong>Phone Number invalid</strong> Please input valid phone number.
        </div>
        <!-- <form class="ps-form--account ps-tab-root" method="POST" action="{{ route('customer.password.request') }}"> -->
        <form class="ps-form--account ps-tab-root">
            <div class="ps-form__content">
                <h4>{{ __('Reset Password') }}</h4>
                <div class="form-group">
                    <input class="form-control" id= "txt-reset-phone" value="{{ old('phone') }}" placeholder="{{ __('Your Phone') }}">
                    <span id= "resetPhoneVal" style= "display: none;" class="text-danger">Phone field is required!</span>
                </div>

                <div class="form-group">
                    <input class="form-control" type="password" name="password" id="txt-resetPassword" placeholder="{{ __('Password') }}">
                    <span id= "resetPassVal" style= "display: none;" class="text-danger">Email field is required!</span>
                </div>
                <div class="form-group">
                    <input class="form-control" type="password" name="password_confirmation" id="txt-resetPassword-confirmation" placeholder="{{ __('Password') }}">
                    <span id= "resetPassConfirmVal" style= "display: none;" class="text-danger">You Must insert the same password!</span>
                </div>

                <div class="form-group submit">
                    <button class="ps-btn ps-btn--fullwidth" type="button" id= "reset">{{ __('Submit') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id= 'verifyForm' class="ps-my-account" style= "display: none;">

    <div class="container">
        <div id= "verifyfailAlert" class="alert alert-warning alert-dismissible fade show" style= "display: none" role="alert">
          <strong>Something went wrong</strong> Verification code is incorrect. Didn't you get verification code? Then resend.
          
        </div>
        <form class="ps-form--account ps-tab-root" >
            <div class="ps-form__content">
                <h4>Verify Phone Code</h4>
                <div class="form-group">
                    <input class="form-control" type="text" name="verifyCode" id="txt-verifyCode" placeholder="verification code">
                    @if ($errors->has('verifyCode'))
                        <span class="text-danger">{{ $errors->first('verifyCode') }}</span>
                    @endif
                </div>

                <div class="form-group submit">
                    <button class="ps-btn ps-btn--fullwidth" id= "verify" type="text">Verify</button>
                </div>

                <div class="form-group">
                    <p class="text-center">Do you want to resend? <a href="javascript:;" id= "resend">resend</a></p>
                </div>
            </div>
            <!-- <div class="ps-form__footer">
                {!! apply_filters(BASE_FILTER_AFTER_LOGIN_OR_REGISTER_FORM, null, \Botble\Ecommerce\Models\Customer::class) !!}
            </div> -->
        </form>
    </div>
</div>

<script>
    var veriel = document.getElementById('verify');
    
    var phoneModified= '';
    if(veriel){
        document.getElementById('verify').addEventListener('click', onVerifyCodeSubmit);
        function onVerifyCodeSubmit(e) {
            e.preventDefault();
            var txtphone= document.getElementById('txt-reset-phone').value;
            var txtpassword= document.getElementById('txt-resetPassword').value;
            var txtrepass= document.getElementById('txt-resetPassword-confirmation').value;
            window.verifyingCode = true;
            var code = document.getElementById('txt-verifyCode').value;
            confirmationResult.confirm(code).then(function (result) {
              // User signed in successfully.
              var user = result.user;
              window.verifyingCode = false;
              window.confirmationResult= null;
              $.ajaxSetup({
                headers: {
                  'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
              });
              $.post( "/passwordReset", { phone: phoneModified, password: txtpassword } ).then(function (response) {
                    window.location.href="/customer/overview";
                      

                  }, function (response) {

                        // error callback
                        console.log(response);

                    });
            }).catch(function (error) {
              // User couldn't sign in (bad verification code?)
              document.getElementById('verifyfailAlert').style.display= "block";

            });
        }
    }
      
    var resetEl= document.getElementById('reset');
    function resend(){
        console.log("resend btn clicked");
        var txtphone= document.getElementById('txt-reset-phone').value;
        var appVerifier = window.recaptchaVerifier;
        firebase.auth().signInWithPhoneNumber(phoneModified, appVerifier)
            .then(function (confirmationResult) {
                window.confirmationResult= confirmationResult;
                // var confirmationResult= JSON.stringify(confirmationResult);
                // window.localStorage.setItem('confirmationResult', confirmationResult);
                // console.log(JSON.parse(window.localStorage.getItem('confirmationResult')));
                // window.location.href= '/phone-verify?phone='+txtphone;
            }).catch(function (error) {
                if(error.code== "auth/invalid-phone-number"){
                    // location.reload();
                    // alert("Phone Number invalid");
                    document.getElementById("phoneFormatAlert").style.display= "block";
                    
                }
                else{
                    // alert("another error occured");
                    console.error("another error in resend", error);
                }
            });
        }
    if(resetEl){
        var txtphone= document.getElementById('txt-reset-phone').value;
        resetEl.addEventListener('click', onResetClick);
        
        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier(reset, {
            'size': 'invisible',
            // 'callback': (response) => {
            //     onResetClick();
            // },
        });
        
        function onResetClick(){
            console.log('one time called');
            var txtphone= document.getElementById('txt-reset-phone').value;
            var txtpassword= document.getElementById('txt-resetPassword').value;
            var txtrepass= document.getElementById('txt-resetPassword-confirmation').value;

            // validation part
            if(document.getElementById('resetForm').style.display!="none"){
                var valFlag= false;
                // if(txtemail== ''){
                //     document.getElementById('emailVal').style.display= "block";
                //     valFlag= true;
                // }
                // else{
                //     document.getElementById('emailVal').style.display= "none";
                // }
                if(txtphone== ''){
                    document.getElementById('resetPhoneVal').style.display= "block";
                    valFlag= true;
                }
                else{
                    document.getElementById('resetPhoneVal').style.display= "none";
                }
                if(txtpassword== ''){
                    document.getElementById('resetPassVal').style.display= "block";
                    valFlag= true;
                }
                else{
                    document.getElementById('resetPassVal').style.display= "none";
                }
                if(txtrepass!= txtpassword){
                    document.getElementById('resetPassConfirmVal').style.display= "block";
                    valFlag= true;
                }
                else{
                    document.getElementById('resetPassConfirmVal').style.display= "none";
                }
                if(valFlag== true){
                    return 0;
                }
                
                if(txtphone.includes('+')){
                    phoneModified= txtphone;
                }
                else{
                    phoneModified= '+88'+txtphone;
                }
                // This is for phone unique
                $.get("/phoneUnique", {'phone': phoneModified})
                    .then(function (response) {

                        // success callback
                        console.log(response);
                        if(response!= "in use"){
                            document.getElementById("phoneNotuseAlert").style.display= "block";
                            // window.localStorage.setItem('valFlag','true');
                            return 0;
                        }
                        else{
                            
                            
                            // recaptchaVerifier.render().then(function(widgetId) {
                            //     window.recaptchaWidgetId = widgetId;

                            // });
                            var appVerifier = window.recaptchaVerifier;
                            firebase.auth().signInWithPhoneNumber(phoneModified, appVerifier)
                                .then(function (confirmationResult) {
                                  window.confirmationResult= confirmationResult;
                                  document.getElementById('resetForm').style.display= "none";
                                  document.getElementById('verifyForm').style.display= "block";
                                  // var confirmationResult= JSON.stringify(confirmationResult);
                                  // window.localStorage.setItem('confirmationResult', confirmationResult);
                                  // console.log(JSON.parse(window.localStorage.getItem('confirmationResult')));
                                  // window.location.href= '/phone-verify?phone='+txtphone;
                                }).catch(function (error) {
                                    if(error.code== "auth/invalid-phone-number"){
                                        // location.reload();
                                        console.log(error.code);
                                        document.getElementById("phoneFormatAlert").style.display= "block";
                                        
                                    }
                                    else{
                                        // alert("another error occured");
                                        console.error("another error", error);
                                    }
                                });
                        }


                    }, function (response) {

                        // error callback
                        console.log(response);

                    });
            }
            
        // console.log('very ok');
        // console.log(signingIn);
        }
    }
    var resendEl= document.getElementById("resend");
    if(resendEl){
        document.getElementById('resend').addEventListener('click', resend);
    }
</script>