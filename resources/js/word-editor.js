class WordEditor {
    constructor(textareaId, options = {}) {
        this.textarea = document.getElementById(textareaId);
        if (!this.textarea) return;

        this.options = {
            minHeight: options.minHeight || '180px',
            placeholder: options.placeholder || '',
            onChange: typeof options.onChange === 'function' ? options.onChange : null,
        };

        this.textarea.style.display = 'none';
        this.buildEditor();
        this.bindEvents();
        this.syncToTextarea();
    }

    buildEditor() {
        this.container = document.createElement('div');
        this.container.className = 'word-editor-container';

        this.toolbar = document.createElement('div');
        this.toolbar.className = 'word-editor-toolbar';
        this.buildToolbar();

        this.editor = document.createElement('div');
        this.editor.className = 'word-editor-content';
        this.editor.contentEditable = true;
        this.editor.style.minHeight = this.options.minHeight;
        if (this.options.placeholder) {
            this.editor.setAttribute('placeholder', this.options.placeholder);
        }
        this.editor.innerHTML = this.textarea.value || '';

        this.container.appendChild(this.toolbar);
        this.container.appendChild(this.editor);
        this.textarea.parentNode.insertBefore(this.container, this.textarea.nextSibling);
    }

    buildToolbar() {
        const groups = [
            [
                { label: 'Undo', cmd: 'undo', title: 'Undo' },
                { label: 'Redo', cmd: 'redo', title: 'Redo' },
            ],
            [
                { label: '<b>B</b>', cmd: 'bold', title: 'Bold' },
                { label: '<i>I</i>', cmd: 'italic', title: 'Italic' },
                { label: '<u>U</u>', cmd: 'underline', title: 'Underline' },
                { label: '<s>S</s>', cmd: 'strikeThrough', title: 'Strike' },
            ],
            [
                { label: 'Bullet', cmd: 'insertUnorderedList', title: 'Bullet List' },
                { label: '1. List', cmd: 'insertOrderedList', title: 'Numbered List' },
                { label: 'Indent', cmd: 'indent', title: 'Indent' },
                { label: 'Outdent', cmd: 'outdent', title: 'Outdent' },
            ],
            [
                { label: 'Kiri', cmd: 'justifyLeft', title: 'Align Left' },
                { label: 'Tengah', cmd: 'justifyCenter', title: 'Align Center' },
                { label: 'Kanan', cmd: 'justifyRight', title: 'Align Right' },
                { label: 'Justify', cmd: 'justifyFull', title: 'Justify' },
            ],
            [
                { label: 'Link', action: () => this.insertLink(), title: 'Insert Link' },
                { label: 'Unlink', cmd: 'unlink', title: 'Remove Link' },
                { label: 'Tabel', action: () => this.insertTable(), title: 'Insert Table' },
            ],
        ];

        groups.forEach((group) => {
            const box = document.createElement('div');
            box.className = 'word-editor-toolbar-group';
            group.forEach((tool) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'word-editor-btn';
                btn.innerHTML = tool.label;
                btn.title = tool.title;
                btn.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    this.editor.focus();
                    if (tool.action) {
                        tool.action();
                    } else if (tool.cmd) {
                        document.execCommand(tool.cmd, false, null);
                    }
                    this.syncToTextarea();
                });
                box.appendChild(btn);
            });
            this.toolbar.appendChild(box);
        });

        const headingSelect = document.createElement('select');
        headingSelect.className = 'word-editor-select';
        headingSelect.innerHTML = `
            <option value="">Paragraph</option>
            <option value="h1">Heading 1</option>
            <option value="h2">Heading 2</option>
            <option value="h3">Heading 3</option>
            <option value="p">Normal</option>
        `;
        headingSelect.addEventListener('change', (e) => {
            if (!e.target.value) return;
            this.editor.focus();
            document.execCommand('formatBlock', false, e.target.value);
            e.target.value = '';
            this.syncToTextarea();
        });
        this.toolbar.appendChild(headingSelect);

        const listStyleSelect = document.createElement('select');
        listStyleSelect.className = 'word-editor-select';
        listStyleSelect.innerHTML = `
            <option value="">List Style</option>
            <option value="1">1,2,3</option>
            <option value="a">a,b,c</option>
            <option value="A">A,B,C</option>
            <option value="i">i,ii,iii</option>
            <option value="I">I,II,III</option>
        `;
        listStyleSelect.addEventListener('change', (e) => {
            const styleType = e.target.value;
            if (!styleType) return;
            this.editor.focus();
            this.applyOrderedListType(styleType);
            e.target.value = '';
            this.syncToTextarea();
        });
        this.toolbar.appendChild(listStyleSelect);
    }

    bindEvents() {
        const sync = () => this.syncToTextarea();
        this.editor.addEventListener('input', sync);
        this.editor.addEventListener('keyup', sync);
        this.editor.addEventListener('paste', () => setTimeout(sync, 0));
        this.editor.addEventListener('blur', sync);
    }

    syncToTextarea() {
        this.textarea.value = this.editor.innerHTML;
        this.textarea.dispatchEvent(new Event('input', { bubbles: true }));
        this.textarea.dispatchEvent(new Event('change', { bubbles: true }));
        if (this.options.onChange) {
            this.options.onChange(this.textarea.value);
        }
    }

    insertLink() {
        const url = prompt('Masukkan URL (https://...)');
        if (!url) return;
        document.execCommand('createLink', false, url);
    }

    insertTable() {
        const rows = parseInt(prompt('Jumlah baris?', '2') || '2', 10);
        const cols = parseInt(prompt('Jumlah kolom?', '2') || '2', 10);
        if (rows <= 0 || cols <= 0) return;
        let html = '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse; width:100%;">';
        for (let r = 0; r < rows; r++) {
            html += '<tr>';
            for (let c = 0; c < cols; c++) {
                html += '<td>&nbsp;</td>';
            }
            html += '</tr>';
        }
        html += '</table><p></p>';
        document.execCommand('insertHTML', false, html);
    }

    applyOrderedListType(type) {
        const sel = window.getSelection();
        if (!sel || sel.rangeCount === 0) return;
        let node = sel.anchorNode;
        if (node && node.nodeType === Node.TEXT_NODE) node = node.parentNode;
        const ol = node ? node.closest('ol') : null;
        if (ol) {
            ol.setAttribute('type', type);
        } else {
            document.execCommand('insertOrderedList', false, null);
            const newOl = node ? node.closest('ol') : this.editor.querySelector('ol:last-of-type');
            if (newOl) newOl.setAttribute('type', type);
        }
    }
}

window.WordEditor = WordEditor;
