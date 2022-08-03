$(function () {
  'use strict';

  /**
   *  generates a still frame image from the stream in the <video>
   *  appends the image to the <body>
   */
  function useCamera(event, video, button1, button2) {
    event.preventDefault();
    video.style.display = "";
    button1.style.display = "none";


    // access the web cam
    navigator.mediaDevices.getUserMedia({ video: true })
    // permission granted:
    .then(function (stream) {
      video.srcObject = stream;
      button2.style.display = "";
    })
    // permission denied:
    .catch(function (error) {
      //
    });
  }

  function takeSnapshot(event, base, video) {
    event.preventDefault();
    var img = document.getElementById(base + "_capture_img");
    var exImg = document.getElementById(base + "_existing_img_section");
    var hidden = document.getElementById(base + "_capture");
    var context;
    var width = video.offsetWidth
      , height = video.offsetHeight;
    const canvas = document.getElementById(base + "_canvas");
    canvas.width = width * 8;
    canvas.height = height * 8;

    context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, width * 8, height * 8);

    img.style.display = "";
    if (exImg) {
      exImg.style.display = "none";
    }
    img.src = canvas.toDataURL('image/png');
    img.width = width;
    img.height = height;
    hidden.value = img.src;
  }

  document.querySelectorAll("[data-form-image-file-capture-base]").forEach(button1 => {

    const base = button1.dataset.formImageFileCaptureBase;
    const video = document.getElementById(base + "_video");
    const button2 = document.getElementById(base + "_button2");
    if (navigator.mediaDevices) {
      button1.style.display = "";
      button2.style.display = "none";
      button1.addEventListener('click', function (event) {
        useCamera(event, video, button1, button2);
      });
      button2.addEventListener('click', function (event) {
        takeSnapshot(event, base, video);
      });
    } else {
      button1.style.display = "none";
      button2.style.display = "none";
    }
  });

});