# Flysystem Meta Adapters
Flysystem uses adapters as an abstraction for storage implementations.
This library contains a set of meta-adapters that take 1 or more adapters as their input and expose them as a normal adapter.

## OverlayAdapter

While Flysystem has a mount manager, this requires explicit knowledge of the mounts on the consumer side. For separating
concerns this is not ideal.
Imagine just exposing an adapter to your code (via a `FileSystem`) and having the magic of different backends be handled
on the configuration side.

The `OverlayAdapter` for example could allow you to have any `Flysystem` compatible file browser and use it to browse a virtual filesystem 
that consists of both real files on S3 storage, real files on local storage and virtual directories pulled from your database.

## DirectoryViaPlaceholderFileAdapter

Some storages don't support directories. Many of the S3 based stores don't have directories as a concept.
This adapter adds supports for directories by using a placeholder file. The idea being that a file in a path defines a directory.

## MetadataCachingAdapter

Some APIs have 1 API call for getting all metadata. This adapter caches that so that you don't see multiple API requests
for calls to `lastModified` and `size` on the same file.
Use this only for adapters that retrieve all metadata for calls to `lastModified` and `mimeType` etc.

## StripPrefixAdapter

Removes a prefix from all paths passed to the adapter. Used in combination with the `OverlayAdapter` to mount one adapter 
inside a path of another.

## AddPrefixAdapter

Adds a prefix to all paths passed to the adapter. Used in combination with the `OverlayAdapter` to mount one adapter
inside a path of another.

## VirtualDirectoryListAdapter

Sometimes you want to have a directory available for each entity in your system, imagine a folder for each user. 
If there are many users getting the list from the storage API might be slow, but you already have the list in your local database.
By using this adapter you can specify a list of directories in a specific path on the underlying adapter.

## OverlayAdapter

Mount one adapter onto another using a path prefix.
