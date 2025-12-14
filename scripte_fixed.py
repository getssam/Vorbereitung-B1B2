from pytube import YouTube, Playlist
import os
import sys

def on_progress(stream, chunk, bytes_remaining):
    """Callback function to show download progress."""
    total_size = stream.filesize
    bytes_downloaded = total_size - bytes_remaining
    percentage_of_completion = bytes_downloaded / total_size * 100
    sys.stdout.write(f"\rDownloading... {percentage_of_completion:.1f}%")
    sys.stdout.flush()

def choose_quality(streams):
    """Lets the user choose a video quality from a list of available streams."""
    # Filter for progressive streams (video + audio) and get unique resolutions
    qualities = sorted(list(set(s.resolution for s in streams if s.resolution)), key=lambda r: int(r[:-1]), reverse=True)
    
    if not qualities:
        print("No progressive (video+audio) streams available.")
        return None

    print("\nAvailable qualities:")
    for i, q in enumerate(qualities, 1):
        print(f"{i}. {q}")

    while True:
        try:
            choice = int(input(f"Choose quality (1-{len(qualities)}): "))
            if 1 <= choice <= len(qualities):
                return qualities[choice - 1]
            else:
                print("Invalid choice. Please try again.")
        except ValueError:
            print("Invalid input. Please enter a number.")

def download_media(yt, file_type, quality, path):
    """Handles the core logic of selecting a stream and downloading."""
    if file_type == "1":  # Video
        stream = yt.streams.filter(progressive=True, file_extension='mp4', res=quality).first()
        # Fallback if the chosen quality is not available for some reason
        if not stream:
            print(f"Quality {quality} not available for '{yt.title}'. Falling back to highest available.")
            stream = yt.streams.filter(progressive=True, file_extension='mp4').order_by('resolution').desc().first()
    else:  # Audio
        stream = yt.streams.filter(only_audio=True).first()

    if not stream:
        print(f"Could not find a suitable stream for '{yt.title}'. Skipping.")
        return

    print(f"\nDownloading: '{yt.title}' ({stream.resolution or 'Audio'})")
    out_file = stream.download(output_path=path)

    if file_type == "2":  # If audio, rename .mp4 to .mp3
        base, _ = os.path.splitext(out_file)
        new_file = base + ".mp3"
        # Check if file exists to avoid error on retry
        if os.path.exists(new_file):
            os.remove(new_file)
        os.rename(out_file, new_file)
        print(f"\nRenamed to: {new_file}")

    print("\nFinished!")

def download_single_video(url, path="downloads"):
    """Downloads a single YouTube video or audio."""
    try:
        yt = YouTube(url, on_progress_callback=on_progress)
        print(f"\nTitle: {yt.title}")

        print("\n1 - Video (MP4, with audio)")
        print("2 - Audio only (MP3)")
        file_type = input("Choose file type: ")

        if file_type not in ["1", "2"]:
            print("Invalid choice. Aborting.")
            return

        quality = None
        if file_type == "1":
            streams = yt.streams.filter(progressive=True, file_extension='mp4')
            quality = choose_quality(streams)
            if not quality:
                return # Stop if no suitable video quality is found

        download_media(yt, file_type, quality, path)

    except Exception as e:
        print(f"\nAn error occurred: {e}\n")

def download_playlist(url, path="downloads"):
    """Downloads a complete YouTube playlist."""
    try:
        pl = Playlist(url)
        print(f"\nPlaylist: {pl.title}")
        print(f"Total videos: {len(pl.video_urls)}")

        print("\n1 - Video (MP4, with audio)")
        print("2 - Audio only (MP3)")
        file_type = input("Choose file type: ")
        
        if file_type not in ["1", "2"]:
            print("Invalid choice. Aborting.")
            return

        quality = None
        if file_type == "1":
            # Check qualities from the first video to set a target for the playlist
            sample_yt = YouTube(pl.video_urls[0])
            streams = sample_yt.streams.filter(progressive=True, file_extension='mp4')
            quality = choose_quality(streams)
            if not quality:
                return # Stop if no suitable video quality is found

        for video_url in pl.video_urls:
            try:
                yt = YouTube(video_url, on_progress_callback=on_progress)
                download_media(yt, file_type, quality, path)
            except Exception as e:
                print(f"\nError downloading {video_url}: {e}. Skipping.")
        
        print("\nAll playlist videos processed!\n")

    except Exception as e:
        print(f"\nAn error occurred with the playlist: {e}")

def main():
    """Main function to run the program."""
    # Ensure the download directory exists
    if not os.path.exists("downloads"):
        os.makedirs("downloads")
        
    while True:
        print("\n--- YouTube Downloader ---")
        print("1 - Download a single video")
        print("2 - Download a playlist")
        print("3 - Exit")
        choice = input("Choose an option: ")

        if choice == "3":
            break
        
        if choice not in ["1", "2"]:
            print("Invalid choice, please try again.")
            continue

        url = input("Enter YouTube URL: ")
        
        if not url:
            print("URL cannot be empty.")
            continue

        if choice == "1":
            download_single_video(url, "downloads")
        else:
            download_playlist(url, "downloads")

if __name__ == "__main__":
    # Note: This script requires the 'pytube' library.
    # Install it using: pip install pytube
    main()
