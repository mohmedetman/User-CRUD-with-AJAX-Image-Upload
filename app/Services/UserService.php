<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;

class UserService
{
    public function index()
    {
        return User::latest()->get();
    }

    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function store(array $data)
    {

        if (isset($data['profile_image'])) {
            $data['image'] = $this->uploadImage($data['profile_image']);
        }

        return User::create($data);
    }

    public function update($id, array $data)
    {
        $user = $this->show($id);

        if (isset($data['profile_image']) && $data['profile_image'] instanceof UploadedFile) {
            if ($user->image) {
                $this->deleteImage($user->image);
            }
            $data['image'] = $this->uploadImage($data['profile_image']);
        } else {
            unset($data['profile_image']);
        }

        $user->update($data);
        return $user->fresh();
    }

    public function delete($id)
    {
        $user = $this->show($id);

        if ($user->image) {
            $this->deleteImage($user->image);
        }

        return $user->delete();
    }

    private function uploadImage(UploadedFile $image): string
    {
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads/users'), $imageName);
        return $imageName;
    }

    private function deleteImage($imageName): void
    {
        $imagePath = public_path('uploads/users/' . $imageName);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
