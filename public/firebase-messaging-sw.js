importScripts('https://www.gstatic.com/firebasejs/8.2.6/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.2.6/firebase-messaging.js');

/*
Initialize the Firebase app in the service worker by passing in the messagingSenderId.
* New configuration for app@pulseservice.com
*/
firebase.initializeApp({
    apiKey: "AIzaSyAlMgdzUQ7wHWwKNCmT_MASniJJQc5abrw",
    authDomain: "rapasshop.firebaseapp.com",
    projectId: "rapasshop",
    storageBucket: "rapasshop.appspot.com",
    messagingSenderId: "389358954785",
    appId: "1:389358954785:web:2eb8ad4e5914c68b163660",
    measurementId: "G-PL7JVWHHKJ"
});

/*
Retrieve an instance of Firebase Messaging so that it can handle background messages.
*/
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  const notificationTitle = 'Background Message Title';
  const notificationOptions = {
    body: 'Background Message body.',
    icon: '/firebase-logo.png'
  };

  return self.registration.showNotification(notificationTitle,
      notificationOptions);
});