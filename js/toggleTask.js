function toggleTask(taskId) {
    // Find the task element and the checkbox
    const taskElement = document.querySelector(`[data-id='${taskId}']`);
    const marker = taskElement.querySelector('.marker');
    const checkbox = taskElement.querySelector('input[type="checkbox"]');

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
                // Update the task appearance based on the server's response
                if (data.newStatus === 1) {
                    // Task completed
                    checkbox.checked = true;
                    marker.style.textDecoration = 'line-through';
                    marker.style.opacity = '0.6';
                    const taskList = document.getElementById('taskList');
                    taskList.appendChild(taskElement); // Move the task to the bottom
                } else {
                    // Task not completed
                    checkbox.checked = false;
                    marker.style.textDecoration = 'none';
                    marker.style.opacity = '1';
                    const taskList = document.getElementById('taskList');
                    taskList.insertBefore(taskElement, taskList.firstChild); // Move the task back up
                }
            } else {
                console.error('Failed to update task status');
            }
        })
        .catch(error => console.error('Error:', error));
}
