window.addEventListener('load', function () {
    document.getElementById('overlay').style.display = 'none';
});

document.addEventListener('DOMContentLoaded', () => {
    var el = document.getElementById('taskList');
    var sortable = Sortable.create(el, {
        handle: '.drag-handle',
        onEnd: function (evt) {
            var order = [];
            el.querySelectorAll('.card').forEach((card, index) => {
                order.push({ id: card.getAttribute('data-id'), order: index + 1 });
            });

            // Send the new order to the server
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "reorderCard.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log('Reorder successful');
                } else if (xhr.readyState === 4) {
                    console.error('Reorder failed', xhr.responseText);
                }
            };
            xhr.send(JSON.stringify(order));
        }
    });
});