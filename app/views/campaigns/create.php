<!-- FILE: /app/views/campaigns/create.php -->
<?php
$pageTitle = 'Create Campaign';
require __DIR__ . '/../layouts/header.php';
?>

<div class="form-container">
    <form method="POST" action="/campaigns/store" class="standard-form">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="name">Campaign Name *</label>
            <input type="text" id="name" name="name" required class="form-control">
        </div>

        <div class="form-group">
            <label>Audience *</label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="audience" value="all" checked onchange="toggleTagSelect()">
                    All Contacts
                </label>
                <label class="radio-label">
                    <input type="radio" name="audience" value="tag" onchange="toggleTagSelect()">
                    Specific Tag
                </label>
            </div>
        </div>

        <div class="form-group" id="tagSelectGroup" style="display: none;">
            <label for="tag_id">Select Tag</label>
            <select id="tag_id" name="tag_id" class="form-control">
                <?php foreach ($tags as $tag): ?>
                    <option value="<?php echo $tag['id']; ?>"><?php echo View::escape($tag['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Message Type *</label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="message_type" value="text" checked onchange="toggleMessageType()">
                    Plain Text
                </label>
                <label class="radio-label">
                    <input type="radio" name="message_type" value="template" onchange="toggleMessageType()">
                    Use Template
                </label>
            </div>
        </div>

        <div class="form-group" id="textMessageGroup">
            <label for="message_body">Message Body</label>
            <textarea id="message_body" name="message_body" rows="6" class="form-control" placeholder="Use {{name}} for personalization"></textarea>
        </div>

        <div class="form-group" id="templateSelectGroup" style="display: none;">
            <label for="template_id">Select Template</label>
            <select id="template_id" name="template_id" class="form-control">
                <?php foreach ($templates as $template): ?>
                    <option value="<?php echo $template['id']; ?>"><?php echo View::escape($template['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="scheduled_at">Schedule (Optional)</label>
            <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="form-control">
            <small>Leave empty to save as draft</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Campaign</button>
            <a href="/campaigns" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
function toggleTagSelect() {
    const isTag = document.querySelector('input[name="audience"]:checked').value === 'tag';
    document.getElementById('tagSelectGroup').style.display = isTag ? 'block' : 'none';
}

function toggleMessageType() {
    const isTemplate = document.querySelector('input[name="message_type"]:checked').value === 'template';
    document.getElementById('textMessageGroup').style.display = isTemplate ? 'none' : 'block';
    document.getElementById('templateSelectGroup').style.display = isTemplate ? 'block' : 'none';
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
