<?php

namespace App\Http\Livewire;

use App\Models\Image;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

class UploadImage extends Component
{
    use WithFileUploads;

    public $gallery;
    public $title;
    public $image;

    protected $rules = [
        'title' => 'required|max:25',
        'image' => 'required|image|max:10000'   //Required - File of type image - maximum file size in KB (here 10 MB).
    ];

    public function render()
    {
        return view('livewire.upload-image');
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    //Save the new image with given title and file
    public function save()
    {
        $data = $this->validate();
        $data['user_id'] = auth()->user()->id;

        DB::transaction(function () {
            //Make and save the image record with an empty path
            $image = new Image;
            $image->title = $this->title;
            $image->gallery_id = $this->gallery->id;
            $image->save();

            //We can finally store the file with its name
            $this->image->storeAs('images', $image->id, 'local');
        });

        //Reset the values of this component to the initial state
        $this->reset(['title', 'image']);

        //Emit a "new image" event to let the list of images refresh itself
        $this->emit("newImageEvent");

        //Set the flashmessage
        session()->flash('uploadImageMessage', 'Image successfully added to this gallery !');
    }
}
