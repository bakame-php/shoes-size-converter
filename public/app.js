'use strict';

const form = document.querySelector('#converter-form');
const result = document.querySelector('#result');
const error = document.querySelector('#error');
const size = document.querySelector('#size');
const decrease = document.querySelector('#size-decrease');
const increase = document.querySelector('#size-increase');
const swapButton = document.querySelector('#swap-units');
const fromSelect = document.querySelector('#unit');
const toSelect = document.querySelector('#to');
const themeButton = document.querySelector('#theme-toggle');

const savedTheme = localStorage.getItem('theme');
const api = window.location.pathname;

const translate = (key) => translations[key] ?? key;
const formatNumber = (value, fractionDigits) =>
  new Intl.NumberFormat(locale, {
    numberingSystem: 'latn',
    minimumFractionDigits: fractionDigits,
    maximumFractionDigits: fractionDigits,
  }).format(value);

const setLocale = (locale) => {
  const params = new URLSearchParams(new FormData(form));
  params.set('lang', locale);

  window.location.href = `${window.location.pathname}?${params}`;
};
const setTheme = (theme) => {
  if (theme === 'light' || theme === 'dark') {
    document.documentElement.dataset.theme = theme;
    localStorage.setItem('theme', theme);
  }
};
const repeatWhilePressed = (button, direction) => {
  if (form.elements.type.value !== 'adult') {
    return;
  }
  let delayTimer;
  let repeatTimer;

  const stop = () => {
    clearTimeout(delayTimer);
    clearInterval(repeatTimer);
  };

  button.addEventListener('pointerdown', () => {
    changeSize(direction);

    delayTimer = setTimeout(() => {
      repeatTimer = setInterval(() => {
        changeSize(direction);
      }, 100);
    }, 400);
  });

  button.addEventListener('pointerup', stop);
  button.addEventListener('pointercancel', stop);
  button.addEventListener('pointerleave', stop);
};
const updateSizeButtons = () => {
  if (form.elements.type.value !== 'adult') {
    return;
  }
  decrease.disabled = Number(size.value) <= Number(size.min);
  increase.disabled = Number(size.value) >= Number(size.max);
};
const swapUnits = () => {
  const from = fromSelect.value;
  fromSelect.value = toSelect.value;
  toSelect.value = from;
  if (form.elements.type.value === 'adult') {
    convert();
  } else {
    sizesFor();
  }
};
const changeSize = (direction) => {
  if (direction < 0) {
    size.stepDown();
  } else {
    size.stepUp();
  }

  size.dispatchEvent(new Event('input', {bubbles: true}));
  updateSizeButtons();
};
const clearErrors = () => {
  error.hidden = true;
  error.replaceChildren();

  form.querySelectorAll('[aria-invalid="true"]').forEach((field) => {
    field.removeAttribute('aria-invalid');
  });

  form.querySelectorAll('.message-error').forEach((message) => {
    message.remove();
  });
};
const showResult = (data) => {
  result.replaceChildren();

  const label = document.createElement('p');
  label.className = 'result-label';
  label.textContent = translate('Result');

  const value = document.createElement('p');
  value.className = 'result-value';

  if (null === data.result) {
    value.textContent = translate('N/A');
  } else {
    const resValue = Number(data.result.value);
    const unit = unitLabels[data.result.unit] ?? data.result.unit;
    value.textContent = `${unit} ${Number.isInteger(resValue) ? resValue : resValue.toFixed(1)}`;
  }

  const measurements = document.createElement('p');
  measurements.className = 'measurements';
  measurements.dir = 'ltr';
  measurements.textContent =
    `${formatNumber(data.measurements.centimeters, 1)} cm · ` +
    `${formatNumber(data.measurements.inches, 2)} in`;

  result.append(label, value, measurements);

  if (data.ranges) {
    const range = document.createElement('p');
    range.className = 'measurements';
    range.dir = 'ltr';

    const label = document.createElement('strong');
    label.dir = 'auto';
    label.textContent = translate('Foot length range:');

    range.append(
      label,
      document.createTextNode(
        ' ' +
        `${formatNumber(data.ranges.centimeters.min, 1)}–` +
        `${formatNumber(data.ranges.centimeters.max, 1)} cm · ` +
        `${formatNumber(data.ranges.inches.min, 2)}–` +
        `${formatNumber(data.ranges.inches.max, 2)} in`,
      ),
    );

    result.append(range);
  }

  result.hidden = false;
};
const showErrors = (problem) => {
  result.hidden = true;
  result.replaceChildren();

  for (const item of problem.errors ?? []) {
    if (item.field === 'convert') {
      error.textContent = translate(item.message);
      error.hidden = false;

      continue;
    }

    const field = form.elements.namedItem(item.field);

    if (!(field instanceof HTMLElement)) {
      continue;
    }

    field.setAttribute('aria-invalid', 'true');

    const message = document.createElement('p');
    message.className = 'message-error';
    message.textContent = translate(item.message);

    field.insertAdjacentElement('afterend', message);
  }
};
const convert = async () => {
  clearErrors();

  const params = new URLSearchParams(new FormData(form));

  try {
    const response = await fetch(`${api}?${params}`, {
      headers: {
        'Accept': 'application/json',
        'Accept-Language': locale,
      },
    });

    const data = await response.json();

    if (!response.ok) {
      showErrors(data);
      return;
    }

    showResult(data);
    history.replaceState(null, '', `?${params}`);
  } catch (exception) {
    console.error(exception);

    result.hidden = true;
    result.replaceChildren();

    error.textContent = translate('Unable to contact the shoe-size converter.');
    error.hidden = false;
  }
};

themeButton.addEventListener('click', () => {
  setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
});
size.addEventListener('input', updateSizeButtons);
swapButton.addEventListener('click', swapUnits);
form.addEventListener('submit', (event) => {
  event.preventDefault();
  convert();
});
document.querySelectorAll('[data-locale]').forEach((link) => {
  link.addEventListener('click', (event) => {
    event.preventDefault();
    setLocale(link.dataset.locale);
  });
});

repeatWhilePressed(decrease, -1);
repeatWhilePressed(increase, 1);
updateSizeButtons();
convert();
setTheme(savedTheme);

const sizesFor = async () => {
  const type = form.elements.type.value;

  if (type !== 'child') {
    return;
  }

  const params = new URLSearchParams({
    type,
    sizes_for: unit.value,
  });

  const response = await fetch(`${window.location.pathname}?${params}`, {
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    return;
  }

  const data = await response.json();

  size.replaceChildren(
    ...data.sizes.map(value => new Option(value, value)),
  );
};

fromSelect.addEventListener('change', sizesFor);
