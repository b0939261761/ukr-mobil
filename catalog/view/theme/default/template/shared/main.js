window.shared = {};

window.shared.throttle = (callback, wait, immediate = false) => {
  let timeout = null;
  let initialCall = true;

  return (...args) => {
    const callNow = immediate && initialCall;

    const run = () => {
      callback(...args);
      timeout = null;
    };

    if (callNow) {
      initialCall = false;
      run();
    }

    if (!timeout) timeout = setTimeout(run, wait);
  };
};

window.shared.debounce = (callback, wait, immediate = false) => {
  let timeout = null;

  return (...args) => {
    const callNow = immediate && !timeout;
    const run = () => callback(...args);

    clearTimeout(timeout);
    timeout = setTimeout(run, wait);

    if (callNow) run();
  };
};

window.shared.escapeRegExp = str => str.replace(/[\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
