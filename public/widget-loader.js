(function () {
    var currentScript = document.currentScript;
    if (!currentScript) {
        var scripts = document.querySelectorAll('script[data-nps-key]');
        currentScript = scripts[scripts.length - 1] || null;
    }

    if (!currentScript) {
        return;
    }

    var publicKey = (currentScript.dataset.npsKey || '').trim();
    var triggerEvent = (currentScript.dataset.npsTrigger || 'on_load').trim();

    if (!publicKey) {
        console.warn('NPS widget: data-nps-key is required.');
        return;
    }

    var scriptUrl = new URL(currentScript.src, window.location.href);
    var apiBase = (currentScript.dataset.npsApiBase || scriptUrl.origin).replace(/\/$/, '');
    var shouldAutoOpen = (currentScript.dataset.npsAutoOpen || 'true').toLowerCase() !== 'false';
    var userIdentifier = (currentScript.dataset.npsUserId || '').trim();
    var sessionIdentifier = (currentScript.dataset.npsSessionId || '').trim();

    var styleId = 'nps-widget-styles';
    var widgetId = 'nps-widget-' + Math.random().toString(16).slice(2);
    var dialogId = widgetId + '-dialog';

    var state = {
        survey: null,
        answers: {},
        errors: {},
        submitError: '',
        submitSuccess: '',
        loading: true,
        submitting: false,
    };
    var widgetRootEl = null;

    function ensureStyles() {
        if (document.getElementById(styleId)) {
            return;
        }

        var style = document.createElement('style');
        style.id = styleId;
        style.textContent = '' +
            '.npsw-open-btn{position:fixed;right:20px;bottom:20px;z-index:9998;padding:12px 16px;border:0;border-radius:999px;background:#0a7b68;color:#fff;font-weight:700;cursor:pointer;box-shadow:0 8px 20px rgba(0,0,0,.2)}' +
            '.npsw-backdrop{position:fixed;inset:0;background:rgba(8,16,30,.6)}' +
            '.npsw-dialog{position:fixed;inset:0;z-index:9999;display:none}' +
            '.npsw-dialog[aria-hidden="false"]{display:block}' +
            '.npsw-panel{position:relative;max-width:640px;margin:6vh auto;background:#fff;border-radius:14px;overflow:hidden;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,sans-serif}' +
            '.npsw-head{padding:18px 20px;background:linear-gradient(135deg,#e8f8f3,#f3f8ff);border-bottom:1px solid #e9edf3}' +
            '.npsw-title{margin:0;font-size:20px;color:#0f2e29}' +
            '.npsw-desc{margin:8px 0 0 0;color:#36524c}' +
            '.npsw-body{padding:18px 20px;max-height:72vh;overflow:auto}' +
            '.npsw-field{margin-bottom:14px}' +
            '.npsw-label{display:block;font-weight:700;margin-bottom:6px;color:#1f2f2a}' +
            '.npsw-help{display:block;font-size:12px;color:#667e77;margin-top:4px}' +
            '.npsw-input,.npsw-select,.npsw-textarea{width:100%;padding:10px;border:1px solid #c8d8d3;border-radius:8px;background:#fff}' +
            '.npsw-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}' +
            '.npsw-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border:1px solid #cbd6e2;border-radius:999px;background:#f8fbff}' +
            '.npsw-actions{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-top:20px}' +
            '.npsw-btn{border:0;border-radius:8px;padding:10px 14px;cursor:pointer;font-weight:700}' +
            '.npsw-btn-main{background:#0a7b68;color:#fff}' +
            '.npsw-btn-muted{background:#eef3f9;color:#29404a}' +
            '.npsw-error{margin:8px 0;padding:10px;border-radius:8px;background:#ffe9e9;color:#7b1d1d}' +
            '.npsw-success{margin:8px 0;padding:10px;border-radius:8px;background:#e8fff2;color:#175c3b}' +
            '.npsw-close{position:absolute;top:10px;right:10px;border:0;background:#ffffff;padding:6px 8px;cursor:pointer;border-radius:6px}' +
            '@media (max-width: 720px){.npsw-panel{margin:0;min-height:100vh;border-radius:0}.npsw-body{max-height:none}}';

        document.head.appendChild(style);
    }

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            if (document.querySelector('script[src="' + src + '"]')) {
                resolve();
                return;
            }

            var script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = function () { resolve(); };
            script.onerror = function () { reject(new Error('Failed to load ' + src)); };
            document.head.appendChild(script);
        });
    }

    function ruleMatches(answer, operator, compareValue) {
        if (answer == null) {
            return false;
        }

        if (operator === 'contains') {
            if (Array.isArray(answer)) {
                return answer.indexOf(compareValue) > -1;
            }
            return String(answer).toLowerCase().indexOf(String(compareValue).toLowerCase()) > -1;
        }

        if (['lt', 'lte', 'gt', 'gte'].indexOf(operator) > -1) {
            var sourceNum = Number(answer);
            var compareNum = Number(compareValue);
            if (Number.isNaN(sourceNum) || Number.isNaN(compareNum)) {
                return false;
            }
            if (operator === 'lt') { return sourceNum < compareNum; }
            if (operator === 'lte') { return sourceNum <= compareNum; }
            if (operator === 'gt') { return sourceNum > compareNum; }
            if (operator === 'gte') { return sourceNum >= compareNum; }
        }

        if (operator === 'eq') {
            return String(answer) === String(compareValue);
        }

        if (operator === 'neq') {
            return String(answer) !== String(compareValue);
        }

        return false;
    }

    function visibleQuestions() {
        var questions = (state.survey && state.survey.questions) || [];
        var rules = (state.survey && state.survey.rules) || [];
        var byTarget = {};

        rules.forEach(function (rule) {
            var key = String(rule.target_question_id);
            if (!byTarget[key]) {
                byTarget[key] = [];
            }
            byTarget[key].push(rule);
        });

        return questions.filter(function (question) {
            var targetRules = byTarget[String(question.id)] || [];
            if (targetRules.length === 0) {
                return true;
            }

            return targetRules.some(function (rule) {
                var source = questions.find(function (q) { return q.id === rule.source_question_id; });
                if (!source) {
                    return false;
                }
                var sourceAnswer = state.answers[source.field_name];
                return ruleMatches(sourceAnswer, rule.operator, rule.compare_value);
            });
        });
    }

    function hasValue(value) {
        if (value == null) {
            return false;
        }
        if (Array.isArray(value)) {
            return value.length > 0;
        }
        return String(value).trim() !== '';
    }

    function inputValue(question) {
        return state.answers[question.field_name];
    }

    function setAnswer(fieldName, value) {
        state.answers[fieldName] = value;
        state.submitError = '';
        state.submitSuccess = '';
        render();
    }

    function validateBeforeSubmit() {
        state.errors = {};
        var valid = true;

        visibleQuestions().forEach(function (question) {
            var value = inputValue(question);
            if (Number(question.is_required) === 1 && !hasValue(value)) {
                state.errors[question.field_name] = 'Campo obrigatorio.';
                valid = false;
            }
        });

        return valid;
    }

    function buildField(van, question) {
        var tags = van.tags;
        var currentValue = inputValue(question);
        var error = state.errors[question.field_name] || '';
        var id = widgetId + '-' + question.field_name;

        function wrap(control) {
            return tags.div({ class: 'npsw-field' },
                tags.label({ class: 'npsw-label', for: id }, question.label + (Number(question.is_required) === 1 ? ' *' : '')),
                control,
                question.help_text ? tags.small({ class: 'npsw-help' }, question.help_text) : null,
                error ? tags.div({ class: 'npsw-error' }, error) : null
            );
        }

        if (question.question_type === 'text') {
            return wrap(tags.input({
                id: id,
                class: 'npsw-input',
                type: 'text',
                value: currentValue || '',
                placeholder: question.placeholder || '',
                oninput: function (e) { setAnswer(question.field_name, e.target.value); }
            }));
        }

        if (question.question_type === 'score_0_10' || question.question_type === 'stars_0_5') {
            return wrap(tags.input({
                id: id,
                class: 'npsw-input',
                type: 'number',
                min: question.scale_min != null ? String(question.scale_min) : '0',
                max: question.scale_max != null ? String(question.scale_max) : '10',
                step: '1',
                value: currentValue != null ? String(currentValue) : '',
                oninput: function (e) { setAnswer(question.field_name, e.target.value); }
            }));
        }

        if (question.question_type === 'select') {
            var selectOptions = [tags.option({ value: '' }, 'Selecione...')].concat((question.options || []).map(function (option) {
                return tags.option({ value: option, selected: currentValue === option }, option);
            }));

            return wrap(tags.select({
                id: id,
                class: 'npsw-select',
                onchange: function (e) { setAnswer(question.field_name, e.target.value); }
            }, selectOptions));
        }

        if (question.question_type === 'radio') {
            return wrap(tags.div({ class: 'npsw-row' }, (question.options || []).map(function (option, idx) {
                var radioId = id + '-r-' + idx;
                return tags.label({ class: 'npsw-chip', for: radioId },
                    tags.input({
                        id: radioId,
                        type: 'radio',
                        name: id + '-group',
                        checked: currentValue === option,
                        onchange: function () { setAnswer(question.field_name, option); }
                    }),
                    option
                );
            })));
        }

        if (question.question_type === 'checkbox') {
            var currentArray = Array.isArray(currentValue) ? currentValue : [];
            return wrap(tags.div({ class: 'npsw-row' }, (question.options || []).map(function (option, idx) {
                var checkId = id + '-c-' + idx;
                return tags.label({ class: 'npsw-chip', for: checkId },
                    tags.input({
                        id: checkId,
                        type: 'checkbox',
                        checked: currentArray.indexOf(option) > -1,
                        onchange: function (e) {
                            var next = Array.isArray(state.answers[question.field_name]) ? state.answers[question.field_name].slice() : [];
                            if (e.target.checked && next.indexOf(option) === -1) {
                                next.push(option);
                            }
                            if (!e.target.checked) {
                                next = next.filter(function (item) { return item !== option; });
                            }
                            setAnswer(question.field_name, next);
                        }
                    }),
                    option
                );
            })));
        }

        return wrap(tags.input({
            id: id,
            class: 'npsw-input',
            type: 'text',
            value: currentValue || '',
            placeholder: question.placeholder || '',
            oninput: function (e) { setAnswer(question.field_name, e.target.value); }
        }));
    }

    function render() {
        var van = window.van;
        if (!van || !state.survey || !widgetRootEl) {
            return;
        }

        var tags = van.tags;
        var root = widgetRootEl;
        root.innerHTML = '';

        if (state.loading) {
            van.add(root, tags.div({ class: 'npsw-body' }, 'Carregando pesquisa...'));
            return;
        }

        var questions = visibleQuestions();

        function onSubmit(event) {
            event.preventDefault();
            state.submitError = '';
            state.submitSuccess = '';

            if (!validateBeforeSubmit()) {
                render();
                return;
            }

            state.submitting = true;
            render();

            fetch(apiBase + '/api/widget/submissions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    public_key: publicKey,
                    trigger_event: triggerEvent,
                    source_url: window.location.href,
                    user_identifier: userIdentifier || null,
                    session_identifier: sessionIdentifier || null,
                    answers: state.answers,
                }),
            })
                .then(function (response) { return response.json(); })
                .then(function (result) {
                    if (!result.success) {
                        state.submitError = 'Nao foi possivel enviar sua resposta.';
                        if (result.errors && typeof result.errors === 'object') {
                            state.errors = result.errors;
                        }
                        state.submitting = false;
                        render();
                        return;
                    }

                    state.submitSuccess = 'Obrigado! Sua resposta foi enviada com sucesso.';
                    state.submitError = '';
                    state.errors = {};
                    state.submitting = false;
                    render();
                })
                .catch(function () {
                    state.submitError = 'Falha de comunicacao ao enviar respostas.';
                    state.submitting = false;
                    render();
                });
        }

        van.add(root,
            tags.div({ class: 'npsw-head' },
                tags.h3({ class: 'npsw-title' }, state.survey.title || state.survey.name || 'Pesquisa NPS'),
                state.survey.description ? tags.p({ class: 'npsw-desc' }, state.survey.description) : null
            ),
            tags.div({ class: 'npsw-body' },
                state.submitError ? tags.div({ class: 'npsw-error' }, state.submitError) : null,
                state.submitSuccess ? tags.div({ class: 'npsw-success' }, state.submitSuccess) : null,
                state.submitSuccess ? null : tags.form({ onsubmit: onSubmit },
                    questions.map(function (question) { return buildField(van, question); }),
                    tags.div({ class: 'npsw-actions' },
                        tags.button({ class: 'npsw-btn npsw-btn-muted', type: 'button', 'data-a11y-dialog-hide': '' }, 'Fechar'),
                        tags.button({ class: 'npsw-btn npsw-btn-main', type: 'submit', disabled: state.submitting }, state.submitting ? 'Enviando...' : 'Enviar')
                    )
                )
            )
        );
    }

    function ensureDialog() {
        ensureStyles();

        var wrapper = document.createElement('div');
        wrapper.innerHTML = '' +
            '<button type="button" class="npsw-open-btn" id="' + widgetId + '-open">Avaliar experiencia</button>' +
            '<div id="' + dialogId + '" class="npsw-dialog" aria-hidden="true" aria-labelledby="' + dialogId + '-title" data-a11y-dialog="true">' +
                '<div class="npsw-backdrop" data-a11y-dialog-hide></div>' +
                '<div class="npsw-panel" role="document">' +
                    '<button class="npsw-close" type="button" data-a11y-dialog-hide>Fechar</button>' +
                    '<div id="' + widgetId + '-root"></div>' +
                '</div>' +
            '</div>';

        document.body.appendChild(wrapper);

        var openButton = document.getElementById(widgetId + '-open');
        var dialogElement = document.getElementById(dialogId);
        widgetRootEl = document.getElementById(widgetId + '-root');

        var dialogApi = null;
        if (window.A11yDialog) {
            dialogApi = new window.A11yDialog(dialogElement);
            dialogApi.hide();
        }

        function openDialog() {
            if (dialogApi) {
                dialogApi.show();
                return;
            }
            dialogElement.setAttribute('aria-hidden', 'false');
        }

        function closeDialog() {
            if (dialogApi) {
                dialogApi.hide();
                return;
            }
            dialogElement.setAttribute('aria-hidden', 'true');
        }

        openButton.addEventListener('click', openDialog);
        dialogElement.addEventListener('click', function (event) {
            var target = event.target;
            if (target && target.hasAttribute('data-a11y-dialog-hide')) {
                closeDialog();
            }
        });

        window.NPSWidget = window.NPSWidget || {};
        window.NPSWidget.open = openDialog;
        window.NPSWidget.close = closeDialog;

        if (shouldAutoOpen) {
            openDialog();
        }
    }

    function bootstrap() {
        Promise.all([
            loadScript('https://cdn.jsdelivr.net/npm/vanjs-core@1.5.2/src/van.min.js'),
            loadScript('https://cdn.jsdelivr.net/npm/a11y-dialog@8.0.0/dist/a11y-dialog.min.js'),
        ])
            .then(function () {
                return fetch(apiBase + '/api/widget/survey?public_key=' + encodeURIComponent(publicKey) + '&trigger_event=' + encodeURIComponent(triggerEvent));
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {
                if (!result.success || !result.data) {
                    throw new Error('Survey schema unavailable');
                }

                state.survey = result.data;
                state.loading = false;
                ensureDialog();
                render();
            })
            .catch(function (error) {
                console.error('NPS widget initialization error:', error);
            });
    }

    bootstrap();
})();
