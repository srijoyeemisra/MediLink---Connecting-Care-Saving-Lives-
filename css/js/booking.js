// booking.js
// Include this file after the existing index.html script (or paste it right after the existing script)
(function(){
  // Use event delegation: catch clicks on .bookBtn anywhere
  document.addEventListener('click', function(e){
    const btn = e.target.closest && e.target.closest('.bookBtn');
    if (!btn) return;
    const id = btn.dataset.id;
    if (!id) return;
    // Redirect to booking page
    // preserve original behavior in case of ctrl/cmd click to open in new tab
    const url = 'book.php?id=' + encodeURIComponent(id);
    if (e.ctrlKey || e.metaKey) {
      window.open(url, '_blank');
    } else {
      window.location.href = url;
    }
  });
})();
