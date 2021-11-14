<!-- here implemented FCM like home -->
<div class="top-menu">
    <ul class="nav navbar-nav float-right">
        @auth
            @if (BaseHelper::getAdminPrefix() != '')
                <li class="dropdown">
                    <a class="dropdown-toggle dropdown-header-name" style="padding-right: 10px" href="{{ url('/') }}" target="_blank"><i class="fa fa-globe"></i> <span @if (isset($themes) && setting('enable_change_admin_theme') != false) class="d-none d-sm-inline" @endif>{{ trans('core/base::layouts.view_website') }}</span> </a>
                </li>
            @endif
            <!-- disabled to simplify -->
            <!-- @if (Auth::check())
                {!! apply_filters(BASE_FILTER_TOP_HEADER_LAYOUT, null) !!}
            @endif -->

            @if (isset($themes) && setting('enable_change_admin_theme') != false)
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle dropdown-header-name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span>{{ trans('core/base::layouts.theme') }}</span>
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right icons-right">

                        @foreach ($themes as $name => $file)
                            @if ($activeTheme === $name)
                                <li class="active"><a href="{{ route('admin.theme', [$name]) }}">{{ Str::studly($name) }}</a></li>
                            @else
                                <li><a href="{{ route('admin.theme', [$name]) }}">{{ Str::studly($name) }}</a></li>
                            @endif
                        @endforeach

                    </ul>
                </li>
            @endif

            <li class="dropdown dropdown-user">
                <a href="javascript:void(0)" class="dropdown-toggle dropdown-header-name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img alt="{{ Auth::user()->getFullName() }}" class="rounded-circle" src="{{ Auth::user()->avatar_url }}" />
                    <span class="username"> {{ Auth::user()->getFullName() }} </span>
                    <i class="fa fa-angle-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('users.profile.view', Auth::user()->getKey()) }}"><i class="icon-user"></i> {{ trans('core/base::layouts.profile') }}</a></li>
                    <li><a href="{{ route('access.logout') }}" class="btn-logout"><i class="icon-key"></i> {{ trans('core/base::layouts.logout') }}</a></li>
                </ul>
            </li>
        @endauth
    </ul>
</div>

<script src="https://www.gstatic.com/firebasejs/8.2.6/firebase.js"></script>
<script>
    $(document).ready(function(){
        console.log('come here?');
        const config = {
            apiKey: "AIzaSyAlMgdzUQ7wHWwKNCmT_MASniJJQc5abrw",
            authDomain: "rapasshop.firebaseapp.com",
            projectId: "rapasshop",
            storageBucket: "rapasshop.appspot.com",
            messagingSenderId: "389358954785",
            appId: "1:389358954785:web:2eb8ad4e5914c68b163660",
            measurementId: "G-PL7JVWHHKJ"
        };
        firebase.initializeApp(config);
        const messaging = firebase.messaging();
        // console.log("Auth::user", "{{Auth::user()}}");
        messaging
            .requestPermission()
            .then(function () {
                
                return messaging.getToken()
            })
            .then(function(token) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.post('/save-admin-device-token', {fcm_token: token}).then(function(response){
                    console.log('response', response);
                }, 
                function(response){
                    console.log('error', response);
                });
                // $.ajax({
                //     url: '{{ URL::to('/save-admin-device-token') }}',
                //     type: 'POST',
                //     data: {
                //         fcm_token: token
                //     },
                //     dataType: 'JSON', 
                //     success: function (response) {
                //         console.log(response)
                //     },
                //     error: function (err) {
                //         console.log(" Can't do because: " + err);
                //     },
                // });
                // console.log("token", token);
            })
            .catch(function (err) {
                console.log("Unable to get permission to notify.", err);
            });
    
        messaging.onMessage(function(payload) {
            console.log('new message arrived', payload);
            const noteTitle = payload.notification.title;
            const noteOptions = {
                body: payload.notification.body,
                vibrate: true
            };
            const notification= new Notification(noteTitle, noteOptions);
            // window.alert("new message here");
            // setTimeout(() => {
            //     notification.close();
            // }, 100 * 1000);
        });
    });
</script>