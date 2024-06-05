<?php

namespace App\Livewire;

use App\Models\Todo;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;

class Todolist extends Component
{
    use WithPagination;

    #[Rule('required|min:3|max:50')]
    public $name;
    
    public $search = '';
    
    public $editingTodoID;
    #[Rule('required|min:3|max:50')]
    public $editingTodoName;

    public function create() 
    {
        // urutan logic : 1.validate, 2.create todo, 3.clear input, 4.send flash message
        
        $validated = $this->validateOnly('name');
        
        try {
            Todo::create($validated);
            $this->reset('name');
            session()->flash('success', 'created berhasil..');
            $this->resetPage();

        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            return;
        }
       
    }
    
    public function delete($todoId)
    {
        try {
            Todo::find($todoId)->delete();
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            return;
        }
    }

    public function edit($todoId)
    {
        try {
            $this->editingTodoID = $todoId;
            $this->editingTodoName = Todo::find($todoId)->name;
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            return;
        }
        
    }

    public function cancelEdit()
    {
        $this->reset('editingTodoID', 'editingTodoName');
    }

    public function update()
    {
        $this->validateOnly('editingTodoName');
        try {

            Todo::find($this->editingTodoID)->update(
                [ 'name' => $this->editingTodoName]
            );
            $this->cancelEdit();

        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            return;
        }
        
    }

    public function toggle($todoId)
    {   
        try {
            $todo = Todo::find($todoId);
            $todo->completed = !$todo->completed;
            $todo->save();
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            return;
        }
      
    }

    public function render()
    {
        return view('livewire.todolist', [
           'todos' => Todo::latest()->where('name','like', '%'.$this->search.'%')->paginate(5),
        ]);
    }
}
