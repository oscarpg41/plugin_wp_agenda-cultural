(function () {
    'use strict';

    var overlay = null;
    var imgEl = null;
    var capEl = null;
    var ultimoFoco = null;

    function crear() {
        overlay = document.createElement('div');
        overlay.className = 'agenda-cultural-lightbox';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-label', 'Imagen ampliada');
        overlay.hidden = true;

        var cerrar = document.createElement('button');
        cerrar.type = 'button';
        cerrar.className = 'agenda-cultural-lightbox-cerrar';
        cerrar.setAttribute('aria-label', 'Cerrar');
        cerrar.innerHTML = '&times;';

        var figura = document.createElement('figure');
        figura.className = 'agenda-cultural-lightbox-figura';
        imgEl = document.createElement('img');
        capEl = document.createElement('figcaption');
        figura.appendChild(imgEl);
        figura.appendChild(capEl);

        overlay.appendChild(cerrar);
        overlay.appendChild(figura);
        document.body.appendChild(overlay);

        overlay.addEventListener('click', function (e) {
            if (e.target !== imgEl) {
                cerrarLightbox();
            }
        });
    }

    function abrir(enlace) {
        if (!overlay) {
            crear();
        }
        var miniatura = enlace.querySelector('img');
        imgEl.src = enlace.getAttribute('href');
        imgEl.alt = miniatura ? miniatura.alt : '';
        capEl.textContent = imgEl.alt;
        ultimoFoco = enlace;
        overlay.hidden = false;
        document.documentElement.classList.add('agenda-cultural-lightbox-abierto');
        overlay.querySelector('button').focus();
    }

    function cerrarLightbox() {
        if (!overlay || overlay.hidden) {
            return;
        }
        overlay.hidden = true;
        imgEl.removeAttribute('src');
        document.documentElement.classList.remove('agenda-cultural-lightbox-abierto');
        if (ultimoFoco) {
            ultimoFoco.focus();
        }
    }

    document.addEventListener('click', function (e) {
        var enlace = e.target.closest ? e.target.closest('a.agenda-cultural-imagen-enlace') : null;
        if (!enlace) {
            return;
        }
        e.preventDefault();
        abrir(enlace);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarLightbox();
        }
    });
})();
