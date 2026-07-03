(function () {
  'use strict';

  var locale = String(window.omnimartLocale || document.documentElement.lang || '').toLowerCase();
  var isBrazilian = locale === 'pt' || locale.indexOf('pt-') === 0 || locale.indexOf('pt_') === 0;

  if (!isBrazilian) {
    return;
  }

  var cepCache = {};

  function digits(value) {
    return String(value || '').replace(/\D/g, '');
  }

  function fieldKey(input) {
    return String(
      (input.getAttribute('name') || '') + ' ' +
      (input.getAttribute('id') || '') + ' ' +
      (input.getAttribute('placeholder') || '') + ' ' +
      (input.className || '')
    ).toLowerCase();
  }

  function isPhoneField(input) {
    var key = fieldKey(input);
    return /\b(phone|telefone|celular|whatsapp|mobile)\b/.test(key);
  }

  function isCepField(input) {
    var key = fieldKey(input);
    return /\b(cep|zip|postal)\b/.test(key);
  }

  function isDocumentField(input) {
    var key = fieldKey(input);
    return /\b(cpf|cnpj|documento|document|tax_id)\b/.test(key);
  }

  function isMoneyField(input) {
    if (String(input.getAttribute('type') || '').toLowerCase() === 'number') {
      return false;
    }

    var key = fieldKey(input);
    return input.hasAttribute('data-mask-money') ||
      input.hasAttribute('data-br-money') ||
      /\b(br-money|money|currency|moeda)\b/.test(key) ||
      /\b(price|amount|valor|preco|minimum_price|discount_price|previous_price|state_price|taxa)\b/.test(key);
  }

  function maskPhone(value) {
    var n = digits(value).slice(0, 11);
    if (n.length <= 10) {
      return n
        .replace(/^(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{4})(\d)/, '$1-$2');
    }

    return n
      .replace(/^(\d{2})(\d)/, '($1) $2')
      .replace(/(\d{5})(\d)/, '$1-$2');
  }

  function maskCep(value) {
    return digits(value).slice(0, 8).replace(/^(\d{5})(\d)/, '$1-$2');
  }

  function maskDocument(value) {
    var n = digits(value).slice(0, 14);
    if (n.length <= 11) {
      return n
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1-$2');
    }

    return n
      .replace(/^(\d{2})(\d)/, '$1.$2')
      .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
      .replace(/\.(\d{3})(\d)/, '.$1/$2')
      .replace(/(\d{4})(\d)/, '$1-$2');
  }

  function parseMoney(value) {
    var text = String(value || '').replace(/[^\d,.-]/g, '');
    if (!text) {
      return '';
    }

    if (text.indexOf(',') !== -1) {
      return text.replace(/\./g, '').replace(',', '.');
    }

    return text;
  }

  function maskMoney(value) {
    var n = digits(value);
    if (!n) {
      return '';
    }

    while (n.length < 3) {
      n = '0' + n;
    }

    var cents = n.slice(-2);
    var integer = n.slice(0, -2).replace(/^0+(?=\d)/, '');
    integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    return 'R$ ' + integer + ',' + cents;
  }

  function applyMask(input) {
    if (!input || input.readOnly || input.disabled) {
      return;
    }

    if (isCepField(input)) {
      input.value = maskCep(input.value);
      fetchCep(input);
      return;
    }

    if (isPhoneField(input)) {
      input.value = maskPhone(input.value);
      return;
    }

    if (isDocumentField(input)) {
      input.value = maskDocument(input.value);
      return;
    }

    if (isMoneyField(input)) {
      input.setAttribute('data-br-money-bound', '1');
      input.value = maskMoney(input.value);
    }
  }

  function setInputValue(form, names, value) {
    if (!value) {
      return;
    }

    for (var i = 0; i < names.length; i++) {
      var selector = '[name="' + names[i] + '"], #' + names[i];
      var field = form.querySelector(selector) || document.querySelector(selector);
      if (!field) {
        continue;
      }

      if (field.tagName === 'SELECT') {
        selectMatchingOption(field, value);
      } else {
        field.value = value;
      }
    }
  }

  function selectMatchingOption(select, value) {
    var normalized = String(value).toLowerCase();
    for (var i = 0; i < select.options.length; i++) {
      var option = select.options[i];
      var text = String(option.text || '').toLowerCase();
      var optionValue = String(option.value || '').toLowerCase();
      if (text === normalized || optionValue === normalized || text.indexOf(normalized) !== -1) {
        select.value = option.value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        break;
      }
    }
  }

  function cepPrefix(input) {
    var name = input.getAttribute('name') || '';
    if (name.indexOf('ship_') === 0) {
      return 'ship';
    }
    if (name.indexOf('bill_') === 0) {
      return 'bill';
    }
    return '';
  }

  function fillAddress(input, data) {
    var form = input.form || document;
    var prefix = cepPrefix(input);
    var prefixPart = prefix ? prefix + '_' : '';

    setInputValue(form, [prefixPart + 'address1', prefix + '-address1', 'address1', 'address'], data.logradouro);
    setInputValue(form, [prefixPart + 'address2', prefix + '-address2', 'address2', 'bairro', 'neighborhood'], data.bairro);
    setInputValue(form, [prefixPart + 'city', prefix + '-city', 'city'], data.localidade);
    setInputValue(form, [prefixPart + 'state', prefixPart + 'uf', 'state', 'uf', 'state_id'], data.uf);
    setInputValue(form, [prefixPart + 'country', prefix + '-country', 'country'], 'Brasil');
  }

  function fetchCep(input) {
    var cep = digits(input.value);
    if (cep.length !== 8 || input.getAttribute('data-last-cep') === cep) {
      return;
    }

    input.setAttribute('data-last-cep', cep);

    if (cepCache[cep]) {
      fillAddress(input, cepCache[cep]);
      return;
    }

    fetch('https://viacep.com.br/ws/' + cep + '/json/')
      .then(function (response) {
        return response.ok ? response.json() : null;
      })
      .then(function (data) {
        if (!data || data.erro) {
          return;
        }
        cepCache[cep] = data;
        fillAddress(input, data);
      })
      .catch(function () {});
  }

  function bindInput(input) {
    if (!input || input.getAttribute('data-br-mask-ready') === '1') {
      return;
    }

    if (!isPhoneField(input) && !isCepField(input) && !isDocumentField(input) && !isMoneyField(input)) {
      return;
    }

    input.setAttribute('data-br-mask-ready', '1');
    applyMask(input);
    input.addEventListener('input', function () {
      applyMask(input);
    });
    input.addEventListener('blur', function () {
      applyMask(input);
    });
  }

  function normalizeMoneyFields(form) {
    var fields = form.querySelectorAll('[data-br-money-bound="1"]');
    fields.forEach(function (field) {
      field.value = parseMoney(field.value);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="text"], input[type="tel"], input[type="number"], input:not([type])').forEach(bindInput);
    document.querySelectorAll('form').forEach(function (form) {
      form.addEventListener('submit', function () {
        normalizeMoneyFields(form);
      });
    });
  });

  document.addEventListener('focusin', function (event) {
    if (event.target && event.target.tagName === 'INPUT') {
      bindInput(event.target);
    }
  });
})();
