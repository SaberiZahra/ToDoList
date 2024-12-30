## Database Schema

### Tables

#### `users`
| Column   | Type    | Description          |
|----------|---------|----------------------|
| `id`     | Integer | Primary key          |
| `isAdmin`| Boolean | Indicates admin role |
| `name`   | String  | Name of the user     |
| `email`  | String  | Email of the user    |

#### `lists`
| Column        | Type    | Description                   |
|---------------|---------|-------------------------------|
| `id`          | Integer | Primary key                   |
| `name`        | String  | Name of the list              |
| `description` | String  | Description of the list       |
| `user_id`     | Integer | Foreign key referencing `users(id)` |

#### `cards`
| Column     | Type    | Description                          |
|------------|---------|--------------------------------------|
| `id`       | Integer | Primary key                          |
| `name`     | String  | Name of the card                     |
| `position` | Integer | Position or order of the card        |
| `deadline` | Date    | Deadline for the card                |
| `user_id`  | Integer | Foreign key referencing `users(id)`  |
| `completed`| Boolean | Indicates if the card is completed   |
