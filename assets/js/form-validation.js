/* 
    DOCUMENTO: JS per validazione form di registrazione
    DESCRIZIONE: Verifica se i campi del form sono corretti 
*/

function showFieldError(input, message) {
    if (!input) return;
    input.classList.add('invalid');
    input.setAttribute('aria-invalid', 'true');
    let errorMsg = input.parentElement.querySelector('.error-message');

    if (!errorMsg) {
        errorMsg = document.createElement('p');
        errorMsg.className = 'error-message';
        errorMsg.setAttribute('role', 'alert');
        errorMsg.setAttribute('aria-live', 'polite');
        input.parentElement.appendChild(errorMsg);
    }

    errorMsg.textContent = message;
    errorMsg.style.display = 'block';
}

function clearFieldError(input) {
    if (!input) return;

    input.classList.remove('invalid');
    input.removeAttribute('aria-invalid');

    const errorMsg = input.parentElement.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.style.display = 'none';
        errorMsg.textContent = '';
    }
}

function clearAllFieldErrors(form) {
    if (!form) return;

    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => clearFieldError(input));
}

function showGeneralError(form, message) {
    if (!form) return;

    let errorBox = form.querySelector('.general-error');

    if (!errorBox) {
        errorBox = document.createElement('div');
        errorBox.className = 'general-error';
        errorBox.setAttribute('role', 'alert');
        errorBox.setAttribute('aria-live', 'assertive');
        errorBox.setAttribute('tabindex', '-1');
        form.insertBefore(errorBox, form.firstChild);
    }

    errorBox.textContent = message;
    errorBox.style.display = 'block';
    errorBox.focus();
}

function clearGeneralError(form) {
    if (!form) return;

    const errorBox = form.querySelector('.general-error');
    if (errorBox) {
        errorBox.style.display = 'none';
        errorBox.textContent = '';
    }
}

function showSuccessMessage(container, message) {
    if (!container) return;

    let successBox = document.querySelector('.success-message');

    if (!successBox) {
        successBox = document.createElement('div');
        successBox.id = 'success-message';
        successBox.className = 'success-message';
        successBox.setAttribute('role', 'status');
        successBox.setAttribute('aria-live', 'assertive');
        successBox.setAttribute('tabindex', '-1');
        container.insertBefore(successBox, container.firstChild);
    }

    successBox.textContent = message;
    successBox.style.display = 'block';
    successBox.focus();
}

function validateNome(nome) {
    const validChars = /^[A-Za-zÀ-ù\s']{2,50}$/;
    return nome.trim() !== "" && validChars.test(nome);
}

function validateCognome(cognome) {
    const validChars = /^[A-Za-zÀ-ù\s']{2,50}$/;
    return cognome.trim() !== "" && validChars.test(cognome);
}

function validateUsername(username) {
    const validChars = /^[a-zA-Z0-9_-]{3,50}$/;
    return username.trim() !== "" && validChars.test(username);
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return email.trim() !== "" && emailRegex.test(email) && email.length <= 100;
}

function validatePassword(password) {
    return password.length >= 8 && /[a-zA-Z]/.test(password) && /[0-9]/.test(password);
}

function handleURLErrors(errorMappings, successMappings) {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');
    const form = document.querySelector('form');
    const main = document.querySelector('main');

    if (error && errorMappings[error]) {
        const errorInfo = errorMappings[error];
        if (errorInfo.field) {

            const input = document.getElementById(errorInfo.field) || document.querySelector(`[name="${errorInfo.field}"]`);
            if (input) {
                showFieldError(input, errorInfo.message);
                input.focus();
            }
        } else {
            if (form) {
                showGeneralError(form, errorInfo.message);
            }
        }
    }
    if (success && successMappings && successMappings[success]) {
        if (main) {
            showSuccessMessage(main, successMappings[success]);
        }
    }
    if (error || success) {
        const cleanUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, cleanUrl);
    }
}
