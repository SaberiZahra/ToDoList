function toggleTask(taskId) {
    // Send the task ID to the server to update the completion status
    fetch('toggleTask.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ taskId })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Find the task element
                const taskElement = document.querySelector(`[data-id='${taskId}']`);
                const marker = taskElement.querySelector('.marker');
                const checkbox = taskElement.querySelector('input[type="checkbox"]');

                // Toggle line-through style based on task completion
                if (checkbox.checked) {
                    marker.style.textDecoration = 'line-through';
                    marker.style.opacity = '0.6';
                    // Move task to the bottom
                    const taskList = document.getElementById('taskList');
                    taskList.appendChild(taskElement); // Move the task to the bottom
                } else {
                    marker.style.textDecoration = 'none';
                    marker.style.opacity = '1';
                    // Move task back up (if needed based on your sorting logic)
                    const taskList = document.getElementById('taskList');
                    taskList.insertBefore(taskElement, taskList.firstChild); // Move the task back to the top
                }
            } else {
                console.error('Failed to update task status');
            }
        })
        .catch(error => console.error('Error:', error));
}
