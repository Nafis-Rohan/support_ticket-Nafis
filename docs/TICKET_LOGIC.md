# Ticket Logic: Mapping, Attend, Forward, and Solved

This document explains how **Engineer Mapping**, **Attend**, **Forward with Note**, and **Solved** work in the support ticket system.

---

## 1. Engineer Mapping (Configuration)

### What it is
**Engineer Mapping** defines **which users (Admin or Engineer) are allowed to work on tickets of which category**. It does **not** auto-assign anyone when a ticket is created.

### Database
- **Table:** `category_engineer_map`
- **Columns:** `id`, `category_id`, `user_id`, `timestamps`
- **Rule:** One row = one user assigned to one category. The same user can be in **multiple categories** (e.g. “Software” and “Hardware”).

### Who configures it
- Only **Admin (role 1)** can open **Config → Engineer Mapping**.
- Admin picks a **category** (e.g. “Software Support”), then:
  - **Add Engineer:** chooses a user (Admin or Engineer) and adds them to this category.
  - **Remove:** removes a user from this category (minus button in the table).

### How it is used
- **Ticket list (Engineer):** An engineer sees only tickets whose **category** they are mapped to (`category_engineer_map`).
- **Ticket details:** The “Action” block (Attend / Forward) is shown only if the current user is in `category_engineer_map` for **that ticket’s category**.
- **Take Action (attend/forward):** Only users in the mapping for that ticket’s category can attend or forward.

So: **Mapping = “who is allowed to see and work on tickets of this category.”**

---

## 2. When a ticket is created

- **No auto-assignment:** `assigned_to` is set to `null`.
- **Status:** `0` (Pending).
- **Who sees it:** All users who are in `category_engineer_map` for that ticket’s **category** see the ticket in their list (and on the dashboard for engineers). So “visibility” is by **category mapping**, not by assignment.

---

## 3. Attend

### What it does
- The ticket gets **one** person working on it: the user who clicked **Attend**.
- That user becomes the **assigned engineer** and the ticket moves to **Processing**.

### Rules
1. Only users who are in **Engineer Mapping for this ticket’s category** can see the Attend button.
2. If the ticket has **no** `assigned_to`, any of those mapped users can click **Attend**.
3. If someone is **already** assigned (`assigned_to` set), others from the same category see “**(Name) is working on this ticket”** and **cannot** attend (until that person forwards or the ticket is solved).

### In code
- **Route:** `POST /tickets/{id}/take-action` with `action=attend`.
- **Checks:** User is in `category_engineer_map` for `tickets.category_id`; ticket has no `assigned_to` or `assigned_to` is the current user.
- **Updates:** `tickets.assigned_to = auth()->id()`, `tickets.status = 1` (Processing).

So: **Attend = “I am taking this ticket; I become the assigned engineer and the ticket becomes Processing.”**

---

## 4. Forward with Note

### What it does
- The **currently attending** engineer hands the ticket to **another** user.
- That other user becomes the new **assigned** engineer.
- An optional **note** is stored as a **reply** on the ticket (e.g. “Forwarded to Joyanta: please check the server logs”).

### Rules
1. Only the user who is **currently** `assigned_to` can forward.
2. The person you forward to must also be in **Engineer Mapping for this ticket’s category** (so only mapped engineers/admins can receive the ticket).
3. You **choose** the target user from a dropdown (other mapped users for that category, excluding yourself).

### In code
- **Route:** Same `POST /tickets/{id}/take-action` with `action=forward`, plus `forward_to` (user id) and optional `note`.
- **Checks:** Current user is `tickets.assigned_to`; `forward_to` exists and is in `category_engineer_map` for this ticket’s category.
- **Updates:** `tickets.assigned_to = forward_to`.
- **Note:** If `note` is not empty, one row is inserted into `ticket_replies` with a message like: `"Forwarded to {name}: {note}"`.

So: **Forward = “I pass this ticket to another mapped engineer; they become assigned; the note is saved as a reply.”**

---

## 5. Solved

### What it does
- The ticket is marked as **Solved** (status = 2).
- Optionally, a **message to the branch** is stored and shown to the branch user.

### Rules
1. Only **Admin (role 1)** or **Engineer (role 2)** can mark a ticket solved (Branch cannot).
2. When status is set to **Solved:**
   - **Solved by:** If the ticket has an `assigned_to`, that user is set as `solved_by`; otherwise the user who clicked “Solved” is stored as `solved_by`.
   - **Optional message:** The “Solved message” / “Message to branch” is stored in `tickets.solved_message` and is shown to the branch in the ticket details.

### In code
- **Route:** `PUT /tickets/{id}` (update) with `status=2` and optional `solved_message`.
- **Updates:** `tickets.status = 2`, `tickets.solved_by` (as above), `tickets.solved_message` (if provided).
- **Logging:** A status change can be recorded in `ticket_status_logs`.

So: **Solved = “This ticket is closed; solved_by is set; optional message to branch is stored and shown to the branch.”**

---

## 6. Flow summary

```
1. Admin configures Engineer Mapping
   → category_engineer_map: which users are in which category

2. Ticket is created (by Branch or Admin)
   → assigned_to = null, status = Pending
   → All users mapped to that ticket’s category see the ticket

3. One of them clicks “Attend”
   → assigned_to = that user, status = Processing
   → Others see “(Name) is working on this ticket”

4. Attending engineer can click “Forward with Note”
   → Chooses another mapped user + optional note
   → assigned_to = new user; note saved as a reply

5. Admin or Engineer marks “Solved” (with optional message to branch)
   → status = Solved, solved_by set, solved_message stored
   → Branch sees the ticket and the message in ticket details
```

---

## 7. Important tables

| Table                   | Purpose |
|-------------------------|--------|
| `category_engineer_map` | Who can see/work on tickets of each category (mapping). |
| `tickets.assigned_to`   | Who is currently working on the ticket (one user). |
| `tickets.status`        | 0 = Pending, 1 = Processing, 2 = Solved. |
| `tickets.solved_by`     | User who is recorded as solver. |
| `tickets.solved_message`| Optional message shown to the branch. |
| `ticket_replies`        | Replies and forward notes (“Forwarded to X: …”). |

All of this logic is implemented in:
- **EngineerMappingController** – mapping (add/remove engineers to/from categories).
- **TicketController** – `index` (who sees which tickets), `takeAction` (attend + forward), `update` (solved).
