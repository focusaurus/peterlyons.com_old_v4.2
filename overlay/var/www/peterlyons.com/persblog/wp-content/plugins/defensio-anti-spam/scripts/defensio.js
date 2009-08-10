  function defensio_toggle_height(id) {
    var p = $('defensio_body_' + id);
    var a = $('defensio_view_full_comment_' + id);
    var shrunkClass = 'defensio_body_shrunk';
    var expandedClass = 'defensio_body_expanded';
    var expandCaption = 'View full comment';
    var shrinkCaption = 'Collapse comment';
  
    if (p.className == shrunkClass) { 
      p.className = expandedClass; 
      a.innerHTML = shrinkCaption;
    } 
    else { 
      p.className = shrunkClass; 
      a.innerHTML = expandCaption;
    }
    return false;
  }
  
  function defensioCheckAll(sender) {
    items = $$('.defensio_comment_checkbox');
    checkboxes = $$('input.defensio_check_all');
    checkFlag = sender.checked;

    for (i = 0; i < items.length; i++) {
      items[i].checked = checkFlag;
    }

    for (i = 0; i < checkboxes.length; i++) {
      if(checkboxes[i] != sender) 
        checkboxes[i].checked = checkFlag;
    }
    return true;
  }
