il.MediaObjectsCompletion = (function() {

  const progress = function(media, currentTime, duration, ended) {
    let completionThreshold = media.dataset.mobCompletionThreshold;
    const completionCallback = media.dataset.mobCompletionCallback;
    const completionCallbackCalled = media.dataset.mobCompletionCallbackCalled;


    console.log("--progress--");
    console.log(media);
    console.log(currentTime);
    console.log(duration);
    console.log(ended);

    if (completionCallback && !completionCallbackCalled) {
      let perc = 0;
      completionThreshold = parseInt(completionThreshold);
      if (currentTime > 0 && duration && duration > 0) {
        perc = 100 / duration * currentTime;
      }
      console.log(perc);
      console.log(completionThreshold);

      if (perc > completionThreshold) {
        fetch(completionCallback, {
          method: 'GET',
          mode: 'same-origin',
          cache: 'no-cache',
          credentials: 'same-origin',
          redirect: 'follow',
          referrerPolicy: 'same-origin'
        });
        media.dataset.mobCompletionCallbackCalled = "1";
      }
    }
  };

  const initVideoAudio = function() {
    document.querySelectorAll(
      "audio:not([data-mob-compl-init]),video:not([data-mob-compl-init])").
    forEach(media => {
      let lastCurrentTime = 0;
      media.addEventListener('ended', function(event){
        progress(media, media.duration, media.duration, true);
      });
      media.addEventListener('timeupdate', function(event) {
        const currentSeconds = Math.floor(media.currentTime);
        if (currentSeconds == lastCurrentTime) {
          return;
        }
        progress(media, media.currentTime, media.duration, false);
        lastCurrentTime = currentSeconds;
      });
      media.dataset.mobComplInit = "1";
    });
  };

  const init = function() {
    initVideoAudio();
  };

  // Return the object that is assigned to Module
  return {
    init: init
  };
}());