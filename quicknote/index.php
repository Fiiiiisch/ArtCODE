<?php
require_once('../auth.php');

$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Note-Taking</title>
    <?php include('../bootstrapcss.php'); ?>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <style>
      .note-item {
        position: relative;
      }
      .delete-btn {
        position: absolute;
        top: 3px;
        right: 3px;
        display: none;
      }
      .note-item:hover .delete-btn {
        display: block;
      }
    </style>
  </head>
  <body>
    <div class="w-100">

      <!-- All Notes View -->
      <div class="container mb-5" id="allNotesView">
        <?php include('../header.php'); ?>
        <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
          <button id="newNoteBtn" class="btn btn-primary fw-medium me-auto"><i class="bi bi-plus-circle"></i> New Note</button>
          <div class="btn-group ms-auto">
            <button id="exportBtn" class="btn btn-secondary fw-medium"><i class="bi bi-download"></i> Export</button>
            <button id="importBtn" class="btn btn-secondary fw-medium"><i class="bi bi-upload"></i> Import</button>
          </div>
        </div>
        <input id="searchInput" class="form-control rounded-pill mb-3 w-100" type="text" placeholder="🔍 Search...">
        <select id="sortSelect" class="form-select fw-medium mb-3" style="width: auto;">
          <option value="newest">Newest</option>
          <option value="oldest">Oldest</option>
        </select>
        <div id="noteList" class="list-group"></div>
      </div>

      <!-- Single Note View -->
      <div id="singleNoteView" style="display: none;">
        <div class="fixed-top bg-dark-subtle py-2">
          <div class="container d-flex justify-content-between align-items-center">
            <button id="backBtn" class="btn border-0 py-0"><i class="bi bi-chevron-left link-body-emphasis" style="-webkit-text-stroke: 2px;"></i></button>
            <h6 id="noteDate" class="text-muted my-auto"></h6>
            <button id="deleteBtn" class="btn border-0 py-0"><i class="bi bi-trash3-fill link-body-emphasis"></i></button>
          </div>
        </div>
        <h2 id="noteTitle" class="mb-3 d-none"></h2>
        <textarea id="noteContent" class="container form-control rounded-0 p-4 pt-5 border-0 vh-100 focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>"></textarea>
      </div>
    </div>

    <input type="file" id="importInput" style="display: none;" accept=".json">

    <script>
      const allNotesView = document.getElementById('allNotesView');
      const singleNoteView = document.getElementById('singleNoteView');
      const newNoteBtn = document.getElementById('newNoteBtn');
      const sortSelect = document.getElementById('sortSelect');
      const noteList = document.getElementById('noteList');
      const noteTitle = document.getElementById('noteTitle');
      const noteDate = document.getElementById('noteDate');
      const noteContent = document.getElementById('noteContent');
      const deleteBtn = document.getElementById('deleteBtn');
      const backBtn = document.getElementById('backBtn');
      const searchInput = document.getElementById('searchInput');
      const exportBtn = document.getElementById('exportBtn');
      const importBtn = document.getElementById('importBtn');
      const importInput = document.getElementById('importInput');

      let notes = {};
      let currentNote = null;
      let autoSaveTimeout = null;

      function loadNotes() {
        const savedNotes = localStorage.getItem('notes');
        if (savedNotes) {
          notes = JSON.parse(savedNotes);
        }
      }

      function saveNotes() {
        localStorage.setItem('notes', JSON.stringify(notes));
      }

      loadNotes();

      newNoteBtn.addEventListener('click', createNote);
      deleteBtn.addEventListener('click', deleteNote);
      backBtn.addEventListener('click', showAllNotes);
      sortSelect.addEventListener('change', updateNoteList);
      searchInput.addEventListener('input', updateNoteList);
      noteContent.addEventListener('input', () => {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(saveNote, 500);
      });
      exportBtn.addEventListener('click', exportNotes);
      importBtn.addEventListener('click', () => importInput.click());
      importInput.addEventListener('change', importNotes);

      function createNote() {
        const noteId = Date.now().toString();
        notes[noteId] = {
          title: 'New Note',
          content: '',
          date: new Date().toISOString()
        };
        currentNote = noteId;
        saveNotes();
        showSingleNote();
      }

      function saveNote() {
        if (currentNote) {
          notes[currentNote].content = noteContent.value;
          notes[currentNote].title = noteContent.value.split('\n')[0] || 'Untitled';
          notes[currentNote].date = new Date().toISOString();
          saveNotes();
          noteTitle.textContent = notes[currentNote].title;
          noteDate.textContent = new Date(notes[currentNote].date).toLocaleDateString('en-US');
          updateNoteList();
        }
      }

      function deleteNote(noteId = currentNote) {
        if (noteId) {
          if (confirm('Are you sure you want to delete this note?')) {
            delete notes[noteId];
            saveNotes();
            if (noteId === currentNote) {
              showAllNotes();
            } else {
              updateNoteList();
            }
          }
        }
      }

      function showAllNotes() {
        allNotesView.style.display = 'block';
        singleNoteView.style.display = 'none';
        currentNote = null;
        updateNoteList();
      }

      function showSingleNote() {
        allNotesView.style.display = 'none';
        singleNoteView.style.display = 'block';
        if (currentNote) {
          noteTitle.textContent = notes[currentNote].title;
          noteDate.textContent = new Date(notes[currentNote].date).toLocaleDateString('en-US');
          noteContent.value = notes[currentNote].content;
        }
      }

      function updateNoteList() {
        noteList.innerHTML = '';
        const query = searchInput.value.toLowerCase();
        const sortedNotes = Object.entries(notes).filter(([id, note]) => 
          note.title.toLowerCase().includes(query) || note.content.toLowerCase().includes(query)
        ).sort((a, b) => {
          const dateA = new Date(a[1].date);
          const dateB = new Date(b[1].date);
          return sortSelect.value === 'newest' ? dateB - dateA : dateA - dateB;
        });

        for (const [noteId, note] of sortedNotes) {
          const noteElem = document.createElement('a');
          noteElem.innerHTML = `
            <h6 class="text-wrap">${note.title}</h6>
            <small class="text-muted">${new Date(note.date).toLocaleDateString('en-US')}</small>
            <button class="btn border-0 btn-sm delete-btn"><i class="bi bi-trash3-fill link-body-emphasis"></i></button>
          `;
          noteElem.href = '#';
          noteElem.className = 'note-item card p-3 rounded-4 border-0 bg-body-tertiary text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> my-1 d-block position-relative text-wrap';
          noteElem.addEventListener('click', (e) => {
            if (!e.target.classList.contains('delete-btn')) {
              e.preventDefault();
              currentNote = noteId;
              showSingleNote();
            }
          });
          noteElem.querySelector('.delete-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            deleteNote(noteId);
          });
          noteList.appendChild(noteElem);
        }
      }

      function exportNotes() {
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(notes));
        const downloadAnchorNode = document.createElement('a');
        downloadAnchorNode.setAttribute("href", dataStr);
        downloadAnchorNode.setAttribute("download", "notes_export.json");
        document.body.appendChild(downloadAnchorNode);
        downloadAnchorNode.click();
        downloadAnchorNode.remove();
      }

      function importNotes(event) {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            try {
              const importedNotes = JSON.parse(e.target.result);
              notes = { ...notes, ...importedNotes };
              saveNotes();
              updateNoteList();
              alert('Notes imported successfully!');
            } catch (error) {
              alert('Error importing notes. Please make sure the file is a valid JSON export.');
            }
          };
          reader.readAsText(file);
        }
      }

      showAllNotes();

      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js')
          .then(registration => {
            console.log('Service Worker registered with scope:', registration.scope);
          }).catch(error => {
            console.log('Service Worker registration failed:', error);
          });
      }
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>