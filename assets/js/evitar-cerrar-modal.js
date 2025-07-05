document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('modal-confirmar');

  // Desactivar cerrar al hacer clic fuera del modal
  modal.addEventListener('click', function (e) {
    if (e.target === modal) {
      // No hacer nada: bloquear salida
      e.stopPropagation();
    }
  });

  // Desactivar cerrar con tecla Escape
  window.addEventListener('keydown', function (e) {
    if (e.key === "Escape") {
      e.preventDefault(); // evita cierre
    }
  });
});