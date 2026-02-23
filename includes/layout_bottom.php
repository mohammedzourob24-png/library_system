</div> <!-- /.content -->

    <footer class="footer">
      <div class="footer-left">© <?php echo date('Y'); ?> Library Management System</div>
      <div class="footer-right">Developed by: Mohammed Khalil Al Mansi</div>
    </footer>

  </div> <!-- /.main -->
</div> <!-- /.container -->


<!-- Global Confirm Delete Modal (works for links + forms) -->
<div id="deleteModal" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <h3 class="modal-title" id="deleteModalTitle">Confirm Delete</h3>
    <p class="modal-text" id="deleteModalText">
      Are you sure you want to delete? This action cannot be undone.
    </p>

    <div class="modal-actions">
      <button type="button" class="btn danger" id="confirmDeleteBtn">Yes, Delete</button>

      <button class="btn" onclick="closeDeleteModal()">Cancel</button>
    </div>
  </div>
</div>

<script>
let deleteUrl = "";
let pendingForm = null;

/* Use for deleting by link (GET) like: delete book */
function openDeleteModal(url, message){
  deleteUrl = url || "";
  pendingForm = null;

  document.getElementById("deleteModalText").textContent =
    message || "Are you sure you want to delete? This action cannot be undone.";

  document.getElementById("deleteModal").style.display = "flex";
}

/* Use for deleting by form (POST) like: delete user */
function openDeleteModalForForm(formEl, message){
  pendingForm = formEl || null;
  deleteUrl = "";

  document.getElementById("deleteModalText").textContent =
    message || "Are you sure you want to delete? This action cannot be undone.";

  document.getElementById("deleteModal").style.display = "flex";
}

function closeDeleteModal(){
  document.getElementById("deleteModal").style.display = "none";
  deleteUrl = "";
  pendingForm = null;
}

document.getElementById("confirmDeleteBtn").addEventListener("click", function(){
  // If we are deleting via POST form
  if (pendingForm) {
    pendingForm.submit();
    return;
  }

  // If we are deleting via link
  if (deleteUrl) {
    window.location.href = deleteUrl;
  }
});

// close when clicking outside the modal box
document.getElementById("deleteModal").addEventListener("click", function(e){
  if (e.target.id === "deleteModal") closeDeleteModal();
});
</script>

</body>
</html>